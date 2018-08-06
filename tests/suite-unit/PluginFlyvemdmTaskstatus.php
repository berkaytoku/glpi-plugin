<?php
/**
 * LICENSE
 *
 * Copyright © 2016-2018 Teclib'
 * Copyright © 2010-2018 by the FusionInventory Development Team.
 *
 * This file is part of Flyve MDM Plugin for GLPI.
 *
 * Flyve MDM Plugin for GLPI is a subproject of Flyve MDM. Flyve MDM is a mobile
 * device management software.
 *
 * Flyve MDM Plugin for GLPI is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * Flyve MDM Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License
 * along with Flyve MDM Plugin for GLPI. If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @author    Domingo Oropeza
 * @copyright Copyright © 2018 Teclib
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/flyve-mdm/glpi-plugin
 * @link      https://flyve-mdm.com/
 * ------------------------------------------------------------------------------
 */

namespace tests\units;

use Flyvemdm\Tests\CommonTestCase;

class PluginFlyvemdmTaskstatus extends CommonTestCase {

   public function beforeTestMethod($method) {
      switch ($method) {
         case 'testPrepareInputForAdd':
         case 'testPrepareInputForUpdate':
         case 'testDisplayTabContentForItem':
         case 'testGetTabNameForItem':
            $this->login('glpi', 'glpi');
            break;
      }
   }

   public function afterTestMethod($method) {
      switch ($method) {
         case 'testPrepareInputForAdd':
         case 'testPrepareInputForUpdate':
         case 'testDisplayTabContentForItem':
         case 'testGetTabNameForItem':
            parent::afterTestMethod($method);
            \Session::destroy();
            break;
      }
   }

   /**
    * @tags testClass
    */
   public function testClass() {
      $class = $this->testedClass->getClass();
      $this->given($class)->string($class::$rightname)->isEqualTo('flyvemdm:taskstatus');
   }

   /**
    * @tags testGetTypeName
    */
   public function testGetTypeName() {
      $instance = $this->newTestedInstance();
      $this->string($instance->getTypeName())->isEqualTo('Task status');
   }

   public function providerPrepareInputForAdd() {
      $policy = new \PluginFlyvemdmPolicy();
      $policy->getFromDbBySymbol('storageEncryption');
      $fleet = $this->createFleet(['name' => $this->getUniqueString()]);
      $task = $this->createTask([
         'value'                       => '0',
         'plugin_flyvemdm_policies_id' => $policy->getID(),
         'itemtype_applied'            => \PluginFlyvemdmFleet::class,
         'items_id_applied'            => $fleet->getID(),
         'itemtype'                    => '',
         'items_id'                    => '',
      ]);

      $validInput = [
         'status'                      => 'pending',
         'plugin_flyvemdm_tasks_id'    => $task->getID(),
         'plugin_flyvemdm_policies_id' => $policy->getID(),
      ];
      return [
         'no status' => [
            'input'    => [],
            'expected' => false,
         ],
         'no plugin_flyvemdm_tasks_id' => [
            'input'    => ['status' => 'pending'],
            'expected' => false,
         ],
         'invalid plugin_flyvemdm_tasks_id' => [
            'input'    => ['status' => 'pending', 'plugin_flyvemdm_tasks_id' => -1],
            'expected' => false,
         ],
         'status null' => [
            'input' => [
               'status'                      => '',
               'plugin_flyvemdm_tasks_id'    => $task->getID(),
               'plugin_flyvemdm_policies_id' => $policy->getID(),
            ],
            'expected' => false,
         ],
         'valid' => [
            'input' => $validInput,
            'expected' => $validInput,
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareInputForAdd
    * @tags testPrepareInputForAdd
    * @engine inline
    * @param array $input
    * @param boolean $expected
    */
   public function testPrepareInputForAdd($input, $expected) {
      $instance = $this->newTestedInstance();
      $output = $instance->prepareInputForAdd($input);
      if ($expected === false) {
         $this->boolean($output)->isFalse();
      } else {
         $this->array($output)->hasKeys(array_keys($expected))
            ->size->isEqualTo(count($expected));
      }
   }

   public function providerPrepareInputForUpdate() {
      return [
         'no status' => [
            'input'    => [],
            'expected' => false,
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareInputForUpdate
    * @tags testPrepareInputForUpdate
    * @engine inline
    * @param array $input
    * @param boolean $expected
    */
   public function testPrepareInputForUpdate($input, $expected) {
      // TODO: try to complete the test for more coverage
      $instance = $this->newTestedInstance();
      $output = $instance->prepareInputForUpdate($input);
      if ($expected === false) {
         $this->boolean($output)->isFalse();
      } else {
         $this->array($output)->hasKeys(array_keys($expected))
            ->size->isEqualTo(count($expected));
      }
   }

   /**
    * @tags testUpdateStatus
    */
   public function testUpdateStatus() {
      $instance = $this->newTestedInstance();
      $policy = new \PluginFlyvemdmPolicyBoolean(new \PluginFlyvemdmPolicy());
      $this->variable($instance->updateStatus($policy, ''))->isNull();
   }

   public function displayTabForItemProvider() {
      return [
         'no tasks for agents' => [
            'item'     => new \PluginFlyvemdmAgent(),
            'expected' => 'There is no task status yet',
         ],
         'no tasks for fleets' => [
            'item'     => new \PluginFlyvemdmFleet(),
            'expected' => 'There is no task status yet',
         ],
      ];
   }

   /**
    * @dataProvider displayTabForItemProvider
    * @tags testDisplayTabContentForItem
    * @param \CommonGLPI $item
    * @param string $expected
    */
   public function testDisplayTabContentForItem($item, $expected) {
      $class = $this->testedClass->getClass();
      ob_start();
      $class::displayTabContentForItem($item);
      $result = ob_get_contents();
      ob_end_clean();
      $this->string($result)->contains($expected);
   }

   public function tabNameForItemProvider() {
      return [
         'for agents' => [
            'item'     => new \PluginFlyvemdmAgent(),
            'expected' => 'Task status',
         ],
         'for fleets' => [
            'item'     => new \PluginFlyvemdmFleet(),
            'expected' => 'Task status',
         ],
         'for invalid item' => [
            'item'     => new \PluginFlyvemdmInvitation(),
            'expected' => '',
         ],
      ];
   }

   /**
    * @dataProvider tabNameForItemProvider
    * @tags testGetTabNameForItem
    * @param \CommonGLPI $item
    * @param string $expected
    */
   public function testGetTabNameForItem($item, $expected) {
      $instance = $this->newTestedInstance();
      $this->string($instance->getTabNameForItem($item))->isEqualTo($expected);
   }
}
