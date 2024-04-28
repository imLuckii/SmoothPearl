<?php

declare(strict_types=1);

namespace imLuckii\SmoothPearl;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\event\EventPriority;
use pocketmine\event\entity\ProjectileHitEvent;

final class Loader extends PluginBase
{

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvent(ProjectileHitEvent::class, static function (ProjectileHitEvent $event): void {
            $projectile = $event->getEntity();
            $entity = $projectile->getOwningEntity();
            if ($projectile instanceof EnderPearl and $entity instanceof Player) {
                $vector = $event->getRayTraceResult()->getHitVector();
                (function () use ($vector, $entity): void {
                    $entity->setPosition($vector);
                })->call($entity);
                $location = $entity->getLocation();
                $entity->getNetworkSession()->syncMovement($location, $location->yaw, $location->pitch);
                $projectile->setOwningEntity(null);
            }
        }, EventPriority::NORMAL, $this);
    }
}
