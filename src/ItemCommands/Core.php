<?php
namespace ItemCommands;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerInteractEvent;

class Core extends PluginBase implements Listener{
	
	/* @var items (array)
	 * contains Item::class (Not pocketmine Item::class)
	 */
	public $items = [];
	
	public function onLoad(){
		 $this->saveDefaultConfig();
	    $this->getLogger()->info("§7Loading items...");
	    foreach($this->getConfig()->getAll() as $stringKey => $data){
	       $this->items[$stringKey] = new Item($stringKey, isset($data["lore"]) ? $data["lore"] : [], $data["name"], $data["commands"], $data["runAs"]);
	       $this->getLogger()->info("§2Loaded ".$this->items[$stringKey]->getName());
	    }
	    $this->getLogger()->info("§aInit process done!");
	}
	
	public function onEnable(){
	    $this->getServer()->getPluginManager()->registerEvents($this, $this);
	    $this->getLogger()->info("§6ItemCommands has been enabled successfully!");
	}
	
	/*
	 * @void onHeld
	 * PlayerItemHeldEvent $event
	 * Priority: HIGHEST
	 */
	
	public function onHeld(PlayerItemHeldEvent $event){
	    $player = $event->getPlayer();
	    foreach($this->items as $key => $class){
	       $class->keepChecking($player);
	    }
	}
	
	/*
	 * @void onTap
	 * PlayerInteractEvent $event
	 * Priority: HIGHEST
	 */
	
	public function onTap(PlayerInteractEvent $event){
	    $player = $event->getPlayer();
	    foreach($this->items as $key => $class){
	       $class->checkExecution($player);
	    }
	}
}
