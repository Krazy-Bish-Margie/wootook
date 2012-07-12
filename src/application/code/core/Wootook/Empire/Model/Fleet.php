<?php
/**
 * This file is part of Wootook
 *
 * @license http://www.gnu.org/licenses/agpl-3.0.txt
 * @see http://wootook.org/
 *
 * Copyright (c) 2011-Present, Grégory PLANCHAT <g.planchat@gmail.com>
 * All rights reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *                                --> NOTICE <--
 *  This file is part of the core development branch, changing its contents will
 * make you unable to use the automatic updates manager. Please refer to the
 * documentation for further information about customizing Wootook.
 *
 */
/**
 *
 * Enter description here ...
 *
 * @uses Wootook\Core\BaseObject
 * @uses Legacies_Empire
 */
class Wootook_Empire_Model_Fleet
    extends Wootook_Core_Mvc_Model_Entity
{
    protected static $_instances = array();

    protected $_eventPrefix = 'fleet';
    protected $_eventObject = 'fleet';

    public static function factory($id)
    {
        if ($id === null) {
            return new self();
        }

        $id = intval($id);
        if (!isset(self::$_instances[$id])) {
            $instance = new self();
            $params = func_get_args();
            call_user_func_array(array($instance, 'load'), $params);
            self::$_instances[$id] = $instance;
        }
        return self::$_instances[$id];
    }

    protected function _init()
    {
        $this->setIdFieldName('fleet_id');
        $this->setTableName('fleets');

        $this->getDataMapper()
            ->addRule('fleet_start_time', 'date-time')
            ->addRule('fleet_end_time', 'date-time')
            ->addRule('fleet_end_stay', 'date-time')
        ;
    }

    public static function planetListener($eventData)
    {
    }

    public function isOwnedBy(Wootook_Player_Model_Entity $player)
    {
        if ($this->getData('fleet_owner') == $player->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @return null|Wootook_Player_Resource_Entity_Collection
     */
    public function getOwnerCollection()
    {
        if ($id = $this->getData('fleet_owner')) {
            $ownerCollection = new Wootook_Player_Resource_Entity_Collection($this->getReadConnection());
            $ownerCollection->addFieldToFilter('id', $id);

            return $ownerCollection;
        }
        return null;
    }

    public function isMission($missionType)
    {
        if ($this->getData('fleet_mission') == $missionType) {
            return true;
        }
        return false;
    }

    public function getRowClass(Wootook_Player_Model_Entity $player = null)
    {
        if ($this->isMission(Legacies_Empire::ID_MISSION_ATTACK) || $this->isMission(Legacies_Empire::ID_MISSION_GROUP_ATTACK)) {
            if ($player !== null && $this->isOwnedBy($player)) {
                return 'attack';
            } else {
                return 'ownattack';
            }
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_TRANSPORT)) {
            return 'transport';
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_STATION) || $this->isMission(Legacies_Empire::ID_MISSION_STATION_ALLY)) {
            return 'station';
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_SETTLE_COLONY)) {
            return 'settle';
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_RECYCLE)) {
            return 'recycle';
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_DESTROY)) {
            return 'destroy';
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_MISSILES)) {
            return 'missiles';
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_EXPEDITION)) {
            return 'expedition';
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_ORE_MINING)) {
            return 'ore-mining';
        }
    }

    public function getMissionLabel()
    {
        if ($this->isMission(Legacies_Empire::ID_MISSION_ATTACK) || $this->isMission(Legacies_Empire::ID_MISSION_GROUP_ATTACK)) {
            return Wootook::__('Attack');
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_TRANSPORT)) {
            return Wootook::__('Transport');
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_STATION) || $this->isMission(Legacies_Empire::ID_MISSION_STATION_ALLY)) {
            return Wootook::__('Station');
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_SETTLE_COLONY)) {
            return Wootook::__('Settle');
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_RECYCLE)) {
            return Wootook::__('Recycle');
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_DESTROY)) {
            return Wootook::__('Destroy');
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_MISSILES)) {
            return Wootook::__('Missiles Launch');
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_EXPEDITION)) {
            return Wootook::__('Expedition');
        } else if ($this->isMission(Legacies_Empire::ID_MISSION_ORE_MINING)) {
            return Wootook::__('Ore mining');
        }
        return Legacies::__('Unknown');
    }

    /**
     * @return Wootook_Core_DateTime
     */
    public function getStartTime()
    {
        return $this->getData('fleet_start_time');
    }

    /**
     * @return Wootook_Core_DateTime
     */

    public function getActionTime()
    {
        return $this->getData('fleet_end_stay');
    }

    /**
     * @return Wootook_Core_DateTime
     */
    public function getArrivalTime()
    {
        return $this->getData('fleet_end_time');
    }

    public function getOriginPlanet()
    {
        $coords = array(
            'galaxy'   => $this->getData('fleet_start_galaxy'),
            'system'   => $this->getData('fleet_start_system'),
            'position' => $this->getData('fleet_start_planet')
            );
        $type = $this->getData('fleet_start_type');

        return Wootook_Empire_Model_Planet::factoryFromCoords($coords, $type);
    }

    public function getOriginPlanetName()
    {
        if (!($planet = $this->getOriginPlanet()->getId())) {
            return '';
        }
        return $planet->getName();
    }

    public function getOriginPlanetCoords()
    {
        if (!($planet = $this->getOriginPlanet()->getId())) {
            return sprintf('%s:%s:%s', $this->getData('fleet_start_galaxy'), $this->getData('fleet_start_system'), $this->getData('fleet_start_planet'));
        }
        return $planet->getCoords();
    }

    public function getDestinationPlanet()
    {
        $coords = array(
            'galaxy'   => $this->getData('fleet_end_galaxy'),
            'system'   => $this->getData('fleet_end_system'),
            'position' => $this->getData('fleet_end_planet')
        );
        $type = $this->getData('fleet_end_type');

        return Wootook_Empire_Model_Planet::factoryFromCoords($coords, $type);
    }

    public function getDestinationPlanetName()
    {
        if (!($planet = $this->getDestinationPlanet()->getId())) {
            return '';
        }
        return $planet->getName();
    }

    public function getDestinationPlanetCoords()
    {
        if (!($planet = $this->getDestinationPlanet()->getId())) {
            return sprintf('%s:%s:%s', $this->getData('fleet_end_galaxy'), $this->getData('fleet_end_system'), $this->getData('fleet_end_planet'));
        }
        return $planet->getCoords();
    }

    public function goBack()
    {
        $this->setData('fleet_mess', 1)->save();

        return $this;
    }

    /**
     * @param Wootook_Empire_Model_Planet $planet
     * @return Wootook_Empire_Model_Fleet
     */
    public function dock(Wootook_Empire_Model_Planet $planet)
    {
        // Backward-compatible way to unserialize fleet ships listing
        $serializedFleetArray = $this->getData('fleet_array');
        foreach (explode(';', $serializedFleetArray) as $fleetShipData) {
            if (empty($fleetShipData)) {
                continue;
            }
            $fleetShipData = explode(',', $fleetShipData);
            if (count($fleetShipData) != 2) {
                continue;
            }
            $planet->setElement($fleetShipData[0], Math::add($planet->getElement($fleetShipData[0]), $fleetShipData[1]));
        }

        $planet
            ->setData('metal',      Math::add($planet->getData('metal'), $this->getData('fleet_resource_metal')))
            ->setData('cristal',    Math::add($planet->getData('cristal'), $this->getData('fleet_resource_crystal')))
            ->setData('deuterium',  Math::add($planet->getData('deuterium'), $this->getData('fleet_resource_deuterium')))
            ->save();
        ;

        $this->delete();

        return $this;
    }
}
