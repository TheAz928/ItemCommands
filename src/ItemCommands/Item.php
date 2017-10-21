<?php
namespace ItemCommands;

use pocketmine\item\Item as IT;

use pocketmine\Server;
use pocketmine\Player;

/* 
 * Developed by TheAz928
 * ItemCommands handler class
 * This software is under GNU General Public License v3.0.0 and later
 */

use pocketmine\command\ConsoleCommandSender;

class Item{

   CONST AS_PLAYER = 0;

   CONST AS_OP = 1;

   CONST AS_CONSOLE = 2;
	
	/* @var stringKey (string) null|id:damage */
	private $stringKey = "";
	
	/* @var lore (array) */
	private $lore = [];
	
	/* @var name (string) */
	private $name = "";
	
	/* @var commands (array) */
	private $commands = [];
	
	/* @var runType (Int) */
	private $runType = 0;
	
	/*
	 * Item(CommandItem) constructor
	 *
	 * String $stringKey
	 * Array $lore
	 * String $name
	 * Array $commands
	 * String $runType
	 */
	
	public function __construct(string $stringKey, array $lore, string $name, array $commands, Int $runType){
	    $this->stringKey = $stringKey;
       $this->lore = $lore;
       $this->name = $name;
       $this->commands = $commands;
       $this->runType = $runType > 2 or $runType < 0 ? 0 : $runType;
	}
	
	/*
	 * @void getItem
	 * return Item
	 */
	
	public function  getItem(): IT{
	    $data = explode(":", $this->stringKey);
	    $meta = isset($data[1]) ? $data[1] : 0;
	    $item = IT::get($data[0], $meta, 1);
	return $item;
	}
	
	/*
	 * @void getLore
	 * return array
	 */
	
	public function getLore(): array{
	    $lore = [];
	    foreach($this->lore as $lor){
	       $lore[] = "§7".$lor."§r";
	    }
	return $lore;
	}
	
	/*
	 * @void getName
	 * return string
	 */
	
	public function getName(): string{
	    $name = "§r".$this->name."§r";
	return $name;
	}
	
	/*
	 * @void getCommands
	 * Player $player
	 * return array
	 */
	
	public function getCommands(Player $player): array{
	    $cmds = [];
	    foreach($this->commands as $key => $cmd){
	      $cmds[] = str_replace(["{name}", "{tag}", "{level}", "{x}", "{y}", "{z}"], [$player->getName(), $player->getNameTag(), $player->getLevel()->getName(), $player->x, $player->y, $player->z], $cmd);
	    }
	return $cmds;
	}
	
	/*
	 * @void getRunType
	 * return string
	 */
	
	public function getRunType(): Int{
	    return $this->runType;
	}
	
	/*
	 * @void keepChecking
	 * Player $player
	 *
	 * Checks player's inventory
	 * if there is command item
	 * but it doesn't have proper 
	 * name or lore
	 *
	 */
	
	public function keepChecking(Player $player): void{
		 $check = $this->getItem();
	    foreach($player->getInventory()->getContents() as $slot => $item){
	       if($item->getId() == $check->getId() and $item->getDamage() == $check->getDamage()){
		      if($item->getName() !== $this->getName() or $item->getLore() !== $this->getLore()){
			     $item->setCustomName($this->getName());
			     $item->setLore($this->getLore());
			     $player->getInventory()->setItem($slot, $item);
			    }
		    }
	    }
	}
	
	/*
	 * @void checkExecution
	 * Player $player
	 *
	 */
	
	public function checkExecution(Player $player): void{
	    $inv = $player->getInventory();
	    $item = $inv->getItemInHand();
	    $check = $this->getItem();
	    if($item->getId() == $check->getId() and $item->getDamage() == $check->getDamage()){
		   if($item->getName() == $this->getName()){ # No need to check lore
			  $commands = $this->getCommands($player);
			  foreach($commands as $cmd){
			    if($this->getRunType() == self::AS_PLAYER){
				   Server::getInstance()->dispatchCommand($player, $cmd);
				 }
				 if($this->getRunType() == self::AS_OP){
					$hadOp = true;
					if($player->isOp() == false){
					 $hadOp = false;
					 $player->setOp(true);
					 Server::getInstance()->getLogger()->debug("Temporarily opped (Running commands as op): ".$player->getName());
					}
					Server::getInstance()->dispatchCommand($player, $cmd);
					if($hadOp == false){
					  $player->setOp(false);
					  Server::getInstance()->getLogger()->debug("Removing op permissions for (Command execution done): ".$player->getName());
					}
				 }
				 if($this->getRunType() == self::AS_CONSOLE){
				   Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), $cmd);
				 }
			  }
			  $item->setCount(1);
			  $inv->removeItem($item);
			  $player->sendTip("§7You've used ".$this->getName());
			}
		}
	}
}