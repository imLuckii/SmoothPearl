<?php

declare(strict_types=1);

namespace imLuckii\SmoothPearl;

use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\utils\Config;

final class Loader extends PluginBase implements Listener
{
    private const CURRENT_CONFIG_VERSION = "1.0";
    private Config $config;

    public function onEnable(): void
    {
        $this->checkAndUpdateConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @param ProjectileHitEvent $event
     * @priority HIGHEST
     */
    public function onProjectileHit(ProjectileHitEvent $event): void
    {
        $projectile = $event->getEntity();
        $entity = $projectile->getOwningEntity();
        
        if ($projectile instanceof EnderPearl && $entity instanceof Player) {
            $vector = $event->getRayTraceResult()->getHitVector();
                (function () use ($vector, $entity): void {
                    $entity->setPosition($vector);
                })->call($entity);
                $location = $entity->getLocation();
                $entity->getNetworkSession()->syncMovement($location, $location->yaw, $location->pitch);
                $projectile->setOwningEntity(null);

            if ($this->config->get("damage", false)) {
                $amount = $this->config->get("amount", 0);
                $entity->attack(new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_FALL, (float)$amount));
            }
        }
    }

    private function checkAndUpdateConfig(): void
    {
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();

        // Check the config version
        $configVersion = $this->config->get("config-version", "");
        if ($configVersion !== self::CURRENT_CONFIG_VERSION) {
            $this->getLogger()->warning("Config version mismatch or missing. Updating to the latest config.");
            $this->replaceConfigWithDefault();
        }
    }

    private function replaceConfigWithDefault(): void
    {
        $configFile = $this->getDataFolder() . "config.yml";

        // Backup the old config if it exists
        if (file_exists($configFile)) {
            $backupFile = $this->getDataFolder() . "config-backup.yml";
            copy($configFile, $backupFile);
        }

        // Save the default config from the resources
        $this->saveResource("config.yml", true);

        // Reload the updated config
        $this->reloadConfig();
        $this->config = $this->getConfig();
    }
}
