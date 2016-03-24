<?php
// +----------------------------------------------------------------------
// | dswjcms
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.tifaweb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
// +----------------------------------------------------------------------
// | Author: 宁波市鄞州区天发网络科技有限公司 <dianshiweijin@126.com>
// +----------------------------------------------------------------------
// | Released under the GNU General Public License
// +----------------------------------------------------------------------
defined('THINK_PATH') or exit();
class FundAction extends AdminCommAction {
//--------用户资金汇总-----------
    public function summary(){
		$Money=D('Money');
		$money=$Money->relation(true)->select();
		$this->assign('money',$money);
		$this->display();
	}
	
	//导出EXCEL(用户资金汇总)
	public function summaryExport(){
		$Money=D('Money');
		$list=$Money->relation(true)->select();
		$data['title']="用户资金汇总";
		$data['name']=array(
							array('n'=>'ID','u'=>'id'),
							array('n'=>'用户ID','u'=>'uid'),
							array('n'=>'用户名','u'=>'username'),
							array('n'=>'总资金','u'=>'total_money'),
							array('n'=>'可用资金','u'=>'available_funds'),
							array('n'=>'冻结资金','u'=>'freeze_funds'),
							array('n'=>'待收资金','u'=>'due_in'),
							array('n'=>'待还资金','u'=>'stay_still'),
							array('n'=>'待收利息','u'=>'stay_interest'),
							array('n'=>'已赚利息','u'=>'make_interest'),
							array('n'=>'已赚奖励','u'=>'make_reward'),
							array('n'=>'逾期金额','u'=>'overdue')
							);
		foreach($list as $l){
			$content[]=array(
							'id'				=>' '.$l['id'],
							'uid'				=>' '.$l['uid'],
							'username'			=>' '.$l['username'],
							'total_money'		=>' '.$l['total_money'],
							'available_funds'	=>' '.$l['available_funds'],
							'freeze_funds'		=>' '.$l['freeze_funds'],
							'due_in'			=>' '.$l['due_in'],
							'stay_still'		=>' '.$l['stay_still'],
							'stay_interest'		=>' '.$l['stay_interest'],
							'make_interest'		=>' '.$l['make_interest'],
							'make_reward'		=>' '.$l['make_reward'],
							'overdue'			=>' '.$l['overdue']
							);
		}
		$data['content']=$content;
		$excel=$this->excelExport($data);
		$this->Record('用户资金汇总导出成功');//后台操作
		$this->success("导出成功","__APP__/TIFAWEB_DSWJCMS/Fund/summary.html");
		
	}

//--------充值-----------
    public function recharge(){
		$recharges=$this->rechargeUser();
		$this->assign('list',$recharges);
		$this->display();
    }
	//充值审核
	public function rechUpda(){
		$recharge=D('Recharge');
		if($create=$recharge->create()){
			$create['handlers']				=$this->_session('admin_name');
			$create['audittime']			=time();
			$result = $recharge->where(array('id'=>$this->_post('id')))->save($create);
			if($result){
				$money=M('money');
				$withd= $recharge->field('uid,account_money,poundage,money')->where(array('id'=>$this->_post('id')))->find();
				$mon=$money->field('total_money,available_funds,freeze_funds')->where(array('uid'=>$withd['uid']))->find();
				if($this->_post('type')==2){	//审核通过
					$array['total_money']				=$mon['total_money']+$withd['account_money'];
					$array['available_funds']			=$mon['available_funds']+$withd['account_money'];	
				}
				//记录添加点
				$money->where(array('uid'=>$withd['uid']))->save($array);
				$this->silSingle(array('title'=>'充值成功','sid'=>$withd['uid'],'msg'=>'充值成功，帐户增加'.$withd['account_money'].'元'));//站内信
				$this->moneyLog(array(0,'充值审核',$withd['money'],'平台',$array['total_money']+$withd['poundage'],$array['available_funds']+$withd['poundage'],$mon['freeze_funds'],$withd['uid']));	//资金记录
				$this->moneyLog(array(0,'充值手续费扣除',$withd['poundage'],'平台',$array['total_money'],$array['available_funds'],$mon['freeze_funds'],$withd['uid']));	//资金记录
				$this->Record('充值审核成功');//后台操作
				$this->success("充值审核成功","__APP__/TIFAWEB_DSWJCMS/Fund/recharge");
			}else{
				$this->silSingle(array('title'=>'充值失败','sid'=>$withd['uid'],'msg'=>'充值失败，流水号有误'));//站内信
				$this->Record('充值审核失败');//后台操作
			$this->error("充值审核失败");
			}		
		}else{
			$this->error($recharge->getError());
		}
    }
	//充值查看页
    public function recharge_page(){
		$recharges=$this->rechargeUser($this->_get('id'));
		$this->assign('list',$recharges);
		$this->display();
    }
//--------提现-----------
    public function withdrawal(){
		$unites=$this->showUser();
		$this->assign('list',$unites);
		$this->display();
    }
	
	//提现审核
	public function withUpda(){
		if($this->_session('verify') != md5(strtoupper($this->_post('proving')))) {
		   $this->error('验证码错误！');
		}
		$withdrawal=D('Withdrawal');
		if($create=$withdrawal->create()){
			$create['handlers']				=$this->_session('admin_name');
			$create['audittime']			=time();
			$result = $withdrawal->where(array('id'=>$this->_post('id')))->save($create);
			if($result){
				$money=M('money');
				$withd= reset($withdrawal->where(array('id'=>$this->_post('id')))->select());
				$mon=reset($money->field('total_money,available_funds,freeze_funds')->where(array('uid'=>$withd['uid']))->select());
				if($this->_post('type')==2){	//审核通过
					$arr['total_money']=$array['total_money']				=$mon['total_money']-$withd['money'];
					$arr['freeze_funds']=$array['freeze_funds']				=$mon['freeze_funds']-$withd['money'];
					$arr['available_funds']=$mon['available_funds'];
					
				}else if($this->_post('type')==3){	//审核不通过
					$arr['total_money']=$mon['total_money'];
					$arr['available_funds']=$array['available_funds']			=$mon['available_funds']+$withd['money'];
					$arr['freeze_funds']=$array['freeze_funds']				=$mon['freeze_funds']-$withd['money'];
				}
				//记录添加点
				$money->where(array('uid'=>$withd['uid']))->save($array);
				$this->silSingle(array('title'=>'提现成功','sid'=>$withd['uid'],'msg'=>'提现成功，帐户减少'.$withd['money'].'元'));//站内信
				$this->moneyLog(array(0,'提现审核',$withd['money'],'平台',$arr['total_money'],$arr['available_funds'],$arr['freeze_funds'],$withd['uid']));	//资金记录
				$this->Record('提现审核成功');//后台操作
				$this->success("提现审核成功","__APP__/TIFAWEB_DSWJCMS/Fund/withdrawal");
			}else{
				$this->silSingle(array('title'=>'提现失败','sid'=>$withd['uid'],'msg'=>'提现失败，银行帐号和户主不统一'));//站内信
				$this->Record('提现审核失败');//后台操作
				$this->error("提现审核失败");
			}		
		}else{
			$this->error($withdrawal->getError());
		}
    }
	
	//提现查看页
    public function withdrawal_page(){
		$unites=$this->showUser($this->_get('id'));
		$this->assign('list',$unites);
		$this->display();
    }
	
//--------资金记录-----------
    public function money(){
		$money=M('money');
		$record=$this->moneyRecord();
		$this->assign('record',$record);
		$this->display();
    }
		
	//导出EXCEL(充值列表)
	public function integExport(){
		$where=$this->_post('type')?"type=".$this->_post('type'):'';
		$list=$this->rechargeUser(0,0,$where);
		$data['title']="充值列表";
		$data['name']=array(
							array('n'=>'订单号','u'=>'nid'),
							array('n'=>'流水号','u'=>'number'),
							array('n'=>'用户名','u'=>'username'),
							array('n'=>'充值类型','u'=>'genre_name'),
							array('n'=>'充值金额','u'=>'money'),
							array('n'=>'手续费','u'=>'poundage'),
							array('n'=>'到账金额','u'=>'account_money'),
							array('n'=>'状态','u'=>'type')
							);
		foreach($list as $l){
			switch($l['type']){
				case '1':
				$type="充值申请";
				break;
				case '2':
				$type="充值成功";
				break;
				case '3':
				$type="充值失败";
				break;
				case '4':
				$type="撤销充值";
				break;
			}
			$content[]=array(
							'nid'				=>' '.$l['nid'],
							'number'			=>' '.$l['number'],
							'username'			=>$l['username'],
							'genre_name'		=>$l['genre_name'],
							'money'				=>$l['money'],
							'poundage'			=>$l['poundage'],
							'account_money'		=>$l['account_money'],
							'type'				=>$type
							);
		}
		$data['content']=$content;
		$excel=$this->excelExport($data);
		$this->Record('充值列表导出成功');//后台操作
			$this->success("导出成功","__APP__/TIFAWEB_DSWJCMS/Fund/entry.html");
		
	}
	
	//导出EXCEL(提现列表)
	public function integExports(){
		$where=$this->_post('type')?"type=".$this->_post('type'):'';
		$list=$this->showUser(0,0,$where);
		$data['title']="提现列表";
		$data['name']=array(
							array('n'=>'用户名','u'=>'username'),
							array('n'=>'真实姓名','u'=>'name'),
							array('n'=>'提现银行','u'=>'bank'),
							array('n'=>'提现支行','u'=>'bank_name'),
							array('n'=>'提现账户','u'=>'bank_account'),
							array('n'=>'提现金额','u'=>'money'),
							array('n'=>'手续费','u'=>'withdrawal_poundage'),
							array('n'=>'到账金额','u'=>'account'),
							array('n'=>'提现时间','u'=>'time'),
							array('n'=>'状态','u'=>'type')
							);
		foreach($list as $l){
			switch($l['type']){
				case '1':
				$type="提现申请";
				break;
				case '2':
				$type="提现成功";
				break;
				case '3':
				$type="提现失败";
				break;
				case '4':
				$type="撤销提现";
				break;
			}
			$content[]=array(
							'username'			=>$l['username'],
							'name'				=>$l['name'],
							'bank'				=>$l['bank'],
							'bank_name'			=>$l['bank_name'],
							'bank_account'		=>" ".$l['bank_account'],
							'money'				=>$l['money'],
							'withdrawal_poundage'=>$l['withdrawal_poundage'],
							'account'			=>$l['account'],
							'time'				=>date('Y-m-d H:i:s',$l['time']),
							'type'				=>$type
							);
		}
		$data['content']=$content;
		$excel=$this->excelExport($data);
		$this->Record('提现列表成功');//后台操作
			$this->success("导出成功","__APP__/TIFAWEB_DSWJCMS/Fund/entry.html");
		
	}
	
	//导出EXCEL(资金记录)
	public function moneyExport(){
		$list=$this->moneyRecord();
		$data['title']="充值列表";
		$data['name']=array(
							array('n'=>'用户名','u'=>'username'),
							array('n'=>'操作金额','u'=>'operation'),
							array('n'=>'总金额','u'=>'total_money'),
							array('n'=>'可用金额','u'=>'available_funds'),
							array('n'=>'冻结金额','u'=>'freeze_funds'),
							array('n'=>'交易对方','u'=>'counterparty'),
							array('n'=>'记录时间','u'=>'time'),
							array('n'=>'操作说明','u'=>'actionname')
							);
		foreach($list as $l){
			$content[]=array(
							'username'				=>$l['username'],
							'operation'				=>$l['operation'],
							'total_money'			=>$l['total_money'],
							'available_funds'		=>$l['available_funds'],
							'freeze_funds'			=>$l['freeze_funds'],
							'counterparty'			=>$l['counterparty'],
							'time'					=>date('Y-m-d H:i:s',$l['time']),
							'actionname'			=>$l['actionname']
							);
		}
		$data['content']=$content;
		$excel=$this->excelExport($data);
		$this->Record('资金记录导出成功');//后台操作
			$this->success("导出成功","__APP__/TIFAWEB_DSWJCMS/Fund/money.html");
		
	}
	
	//--------其它费用操作-----------
    public function other(){
		if($this->_post('change')){
			$models = new Model();
			$y_price=$models->check($this->_post('price'),'number'); 
			$y_uid=$models->check($this->_post('uid'),'number'); 
			$y_explain=$models->check($this->_post('explain'),'require'); 
			if(!$y_price || !$y_uid || !$y_explain){
				$this->error("提交的参数有误！");
			}
			$user=M('user');
			$use=$user->where('id="'.$this->_post('uid').'"')->find();
			if($use){
				$Money=M('money');
				$money=$Money->where('uid="'.$this->_post('uid').'"')->find();
				if($this->_post('change')==1){	//奖励
					$models->query("UPDATE `ds_money` SET `total_money` = `total_money`+".$this->_post('price').", `available_funds` = `available_funds`+".$this->_post('price')." WHERE `uid` =".$this->_post('uid'));
				}else{
					if($this->_post('price')>$money['available_funds']){	//如果操作金额超过用户可用资金
						$this->error("操作金额超出用户可用资金！");
					}
					$models->query("UPDATE `ds_money` SET `total_money` = `total_money`-".$this->_post('price').", `available_funds` = `available_funds`-".$this->_post('price')." WHERE `uid` =".$this->_post('uid'));
				}
				//记录添加点
				$money=$Money->where('uid="'.$this->_post('uid').'"')->find();
				$this->Record($this->_post('explain'));//后台操作
				$moneyLog=$this->moneyLog(array(0,$this->_post('explain'),$this->_post('price'),'平台',$money['total_money'],$money['available_funds'],$money['freeze_funds'],$this->_post('uid')));	//资金记录
				$this->silSingle(array('title'=>$this->_post('explain'),'sid'=>$use['username'],'msg'=>$this->_post('explain')));//站内信
				$arr['member']=array('uid'=>$this->_post('uid'),'name'=>'mem_other');
				
				//$integralAdd=$this->integralAdd($arr);	//积分操作
				$this->success("操作成功","__APP__/TIFAWEB_DSWJCMS/Fund/other");
			}else{
				$this->error("用户不存在！");
			}
		}else{
			$this->display();
		}
	}
}
?>