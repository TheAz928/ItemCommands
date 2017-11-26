<?php
namespace ItemCommands;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\utils\TextFormat as TF;

class Core extends PluginBase implements Listener{
	
	/**
	 * @var Item[] Not pocketmine\item\Item!
	 */
	public $items = [];
	
	public function onLoad(){
		$this->saveDefaultConfig();
		$this->getLogger()->info(TF::GRAY."Loading items...");
		foreach($this->getConfig()->getAll() as $stringKey => $data){
			if($stringKey === "settings"){
				continue;
			}
			$this->items[$stringKey] = new Item($this, $stringKey, isset($data["lore"]) ? $data["lore"] : [], $data["name"], $data["commands"], $data["runAs"]);
			$this->getLogger()->info(TF::DARK_GREEN."Loaded ".$this->items[$stringKey]->getName());
		}
	}
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	/**
	 * @param PlayerItemHeldEvent $event
	 * @priority HIGHEST
	 */
	public function onHeld(PlayerItemHeldEvent $event): void{
		$player = $event->getPlayer();
		foreach($this->items as $key => $class){
			$class->keepChecking($player);
		}
	}
	
	/**
	 * @param PlayerInteractEvent $event
	 * @priority HIGHEST
	 */
	public function onTap(PlayerInteractEvent $event): void{
		$player = $event->getPlayer();
		foreach($this->items as $key => $class){
			$class->checkExecution($player);
		}
	}
}