<?php
namespace ItemCommands;

use pocketmine\item\Item as IT;

use pocketmine\Server;
use pocketmine\Player;

/* 
* Developed by TheAz928
* ItemCommands handler class
* This software is under GNU General Public License v3.0.0 or later
*/

use pocketmine\command\ConsoleCommandSender;

class Item{

	CONST AS_PLAYER = 0;

	CONST AS_OP = 1;

	CONST AS_CONSOLE = 2;
	
	/** @var string|null (id:damage) */
	private $stringKey = "";
	
	/** @var array */
	private $lore = [];
	
	/** @var string */
	private $name = "";
	
	/** @var array */
	private $commands = [];
	
	/** @var int */
	private $runType = 0;
	
	/**
	 * Item(CommandItem) constructor
	 *
	 * @param String $stringKey
	 * @param array $lore
	 * @param string $name
	 * @param array $commands
	 * @param int $runType
	 */
	public function __construct(string $stringKey, array $lore, string $name, array $commands, Int $runType){
		$this->stringKey = $stringKey;
		$this->lore = $lore;
		$this->name = $name;
		$this->commands = $commands;
		$this->runType = $runType > 2 or $runType < 0 ? 0 : $runType;
	}
	
	/**
	 * @return IT
	 */
	public function  getItem(): IT{
		$data = explode(":", $this->stringKey);
		$meta = isset($data[1]) ? $data[1] : 0;
		$item = IT::get($data[0], $meta, 1);
		return $item;
	}
	
	/**
	 * @return array
	 */
	public function getLore(): array{
		$lore = [];
		foreach($this->lore as $lor){
			$lore[] = "§7".$lor."§r";
		}
		return $lore;
	}
	
	/**
	 * @return string
	 */
	public function getName(): string{
		$name = "§r".$this->name."§r";
		return $name;
	}
	
	/**
	 * @param Player $player
	 * @return array
	 */
	public function getCommands(Player $player): array{
		$cmds = [];
		foreach($this->commands as $key => $cmd){
			$cmds[] = str_replace(["{name}", "{tag}", "{level}", "{x}", "{y}", "{z}"], [$player->getName(), $player->getNameTag(), $player->getLevel()->getName(), $player->x, $player->y, $player->z], $cmd);
		}
		return $cmds;
	}
	
	/**
	 * @return int
	 */
	public function getRunType(): int{
		return $this->runType;
	}
	
	/**
	 * @param Player $player
	 *
	 * Checks player's inventory if there is this CommandItem
	 * but it doesn't have proper name or lore.
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
	
	/**
	 * @param Player $player
	 */
	public function checkExecution(Player $player): void{
		$inv = $player->getInventory();
		$item = $inv->getItemInHand();
		$check = $this->getItem();
		$hadOp = true;
		if($item->getId() == $check->getId() and $item->getDamage() == $check->getDamage()){
			if($item->getName() == $this->getName()){ # No need to check lore
				$commands = $this->getCommands($player);
				if($player->isOp() == false and $this->getRunType() == self::AS_OP){
					$hadOp = false;
					$player->setOp(true);
				}
				foreach($commands as $cmd){
					if($this->getRunType() == self::AS_PLAYER or $this->getRunType() == self::AS_OP){
						Server::getInstance()->dispatchCommand($player, $cmd);
					}
					if($this->getRunType() == self::AS_CONSOLE){
						Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), $cmd);
					}
				}
				if($hadOp == false){
					$player->setOp(false);
				}
				$item->setCount(1);
				$inv->removeItem($item);
				$player->sendTip("§7You've used ".$this->getName());
			}
		}
	}
}
