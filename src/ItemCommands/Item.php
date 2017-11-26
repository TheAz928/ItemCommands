<?php
namespace ItemCommands;

use pocketmine\item\Item as IT;

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
	
	/** @var Core */
	private $plugin;
	
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
	 * @param Core $plugin
	 * @param string $stringKey
	 * @param array $lore
	 * @param string $name
	 * @param array $commands
	 * @param int $runType
	 */
	public function __construct(Core $plugin, string $stringKey, array $lore, string $name, array $commands, int $runType){
		$this->plugin = $plugin;
		$this->stringKey = $stringKey;
		$this->lore = $lore;
		$this->name = $name;
		$this->commands = $commands;
		$this->runType = ($runType > 2 or $runType < 0) ? self::AS_PLAYER : $runType;
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
		$hadOp = false;
		if($item->getId() == $check->getId() and $item->getDamage() == $check->getDamage()){
			if($item->getName() == $this->getName()){ # No need to check lore
				$commands = $this->getCommands($player);
				if(!$player->isOp() and $this->runType == self::AS_OP){
					$hadOp = true;
					$player->setOp(true);
				}
				foreach($commands as $cmd){
					if($this->runType == self::AS_PLAYER or $this->runType == self::AS_OP){
						$this->plugin->getServer()->dispatchCommand($player, $cmd);
					}
					if($this->runType == self::AS_CONSOLE){
						$this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
					}
				}
				if($hadOp){
					$player->setOp(false);
				}
				if($this->plugin->getConfig()->getNested('settings.remove-items')){
					$item->setCount(1);
					$inv->removeItem($item);
				}
				$player->sendTip("§7You've used ".$this->getName());
			}
		}
	}
}
