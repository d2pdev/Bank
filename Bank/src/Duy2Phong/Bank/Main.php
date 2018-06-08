<?php

namespace Duy2Phong\B;
/*
 *
 * @author Duy2Phong
 * @link https://poggit.pmmp.io/ci/d2pdev/Bank/Bank
 *
 *
*/
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use onebone\economyapi\EconomyAPI;
use pocketmine\utils\Config;

class Main extends PluginBase{
   public function onEnable()
    {
			$this->getLogger()->info("§2Enabled !");
        if(!is_dir($this->getDataFolder()))
	{
        mkdir($this->getDataFolder());
        }
        $this->bank = new Config($this->getDataFolder() ."bank.yml", Config::YAML, []);
        $this->eco = EconomyAPI::getInstance();
    }
	public function taoNguoiDung($ten){
    $ten = strtolower($ten);
		$this->bank->set($ten,0);
		$this->bank->save();
	}
	public function congTien($ten,$tien){
    $ten = strtolower($ten);
		$tienhienco = $this->bank->get($ten);
		$this->bank->set($ten,$tienhienco + $tien);
		$this->bank->save();
	}
	public function truTien($ten,$tien){
    $ten = strtolower($ten);
		$this->congTien($ten,-$tien);
	}
	public function caiTien($ten,$tien){
    $ten = strtolower($ten);
		$this->bank->set($ten,$tien);
		$this->bank->save();
	}
	public function xemTien($ten){
    $ten = strtolower($ten);
		if($this->kiemTra($ten)){
		$tienhienco = $this->bank->get($ten);
		return $tienhienco;
		}
	    return false;
	}
	public function kiemTra( $ten){
    $ten = strtolower($ten);
		if($this->bank->exists($ten)){
			return true;
		}
		return false;
	}
	public function onCommand(CommandSender $sender, Command $command, string $label, array $ar) : bool{
		switch($command->getName()){
			case "bank":
				if(isset($ar[0])){
					$ten = $sender->getName();
					$all = $this->bank>getAll();
					$money = $this->eco->myMoney($ten);
					if(!$this->kiemTra($ten)){
					$this->taoNguoiDung($ten);
					}
					if($ar[0] == 'seemoney'){
						$tienhienco = $this->xemTien($ten);
						$sender->sendMessage("§fThe amount available in the bank is §a$tienhienco");
						return true;
					}
					if($ar[0] == 'version' or $ar[0] == 'ver'){
						$sender->sendMessage('§f-> §2Bank§f <-');
						$sender->sendMessage('Current version : §e1.1.1');
						$sender->sendMessage('author : Duy2Phong ');
					  $sender->sendMessage('Update to the latest version at : https://poggit.pmmp.io/ci/d2pdev/Bank/Bank');
						return true;
					}
					if($ar[0] == 'help'){
						$sender->sendMessage('§2===Bank==');
						$sender->sendMessage('/bank sendmoney money (Send money to the bank)');
						$sender->sendMessage('/bank takemoney money (withdrawals from bank)');
						$sender->sendMessage('/bank transfers money player (Transfer money from bank to other players)');
						$sender->sendMessage('/bank seemoney (See the amount available in the bank)');
						$sender->sendMessage('/bank version (See the bank version)');
						$sender->sendMessage('§2===============');
						return true;
					}

					if(isset($ar[1])){
						$tien = $ar[1];
						if(!is_numeric($tien)){
							$sender->sendMessage('');
							return false;
						}
						$tien = round($tien,3);
						switch($ar[0]){
							case "sendmoney":
							if($money >= $tien){
								$this->congTien($ten,$tien);
								$this->eco->reduceMoney($ten, $tien);
								$sender->sendMessage("§fYou have sent §a$tien §fto the Bank!");
								return true;
							}
							$sender->sendMessage("§cThe more money you send than you currently have !");
							break;
							case "takemoney":
							if($this->xemTien($ten) >= $tien){
								$this->truTien($ten,$tien);
								$this->eco->addMoney($ten,$tien);
								$tien = (string)$tien;
								$sender->sendMessage("§fYou have take §a$tien §fto the Bank !");
								return true;
							}
							else
								$sender->sendMessage("§The more money you withdraw than you currently have !");
							break;
              case "transfers":
                if($this->kiemTra($ten)){
                  if($this->xemTien($ten) >= strtolower($tien)){
                    if(isset($ar[2])){
                      $this->truTien($ten,$tien);
                      $this->congTien($ar[2],$tien);
                        foreach($this->getServer()->getOnlinePlayers() as $p){
                          if(strtolower($ar[2]) == strtolower($p->getName())){
                            $nguoinhan = $p;
                            break;
                          }
                        }
                      if(isset($nguoinhan)){
                        $nguoinhan->sendMessage("$ten §fhas transferred to you §a$t");
                        return true;
                      }
                      $sender->sendMessage("$ar[2] §fIt is not online yet but the money has been successfully transferred !");
                      return true;
                    }
                  }
                  $sender->sendMessage("§eThe amount of money in your account is not enough to make this transaction !");
                  return true;
                }
                $sender->sendMessage("$ar[2] §cdoes not exist in the bank data !");
              break;
						}
					}
				}
			break;
			default:
				break;
		}
    return false;
	}

	public function onDisable(){
		$this->getLogger()->info("§cĐDisable !");
	}
}
