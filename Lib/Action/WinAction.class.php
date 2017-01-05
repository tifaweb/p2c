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
class WinAction extends CommAction{
	protected function _initialize(){
		//禁止非微信访问
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		/*if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false ){
			echo "Not allowed to access!";
			echo '<br/>
			<script language="JavaScript">
			function myrefresh()
			{
				   window.location.href="http://www.dswjcms.com";
			}
			setTimeout("myrefresh()",2000); //指定1秒刷新一次
			</script>
			';
			exit;
		}*/
		$this->webScan();//安全检测记录
		header("Content-Type:text/html; charset=utf-8");
		$dirname = F('mdirname')?F('mdirname'):"Default";
		C('DEFAULT_THEME','template/'.$dirname);	//自动切换模板
		C('TMPL_ACTION_ERROR','Index/jump');	//默认错误跳转对应的模板文件
		C('TMPL_ACTION_SUCCESS','Index/jump');	//默认成功跳转对应的模板文件
		$system=$this->systems();
		$this->assign('s',$system);
		//缓存
		if($this->_cookie('user_uid')>0){
			session('user_uid',$this->_cookie('user_uid'),604800);
			session('user_name',$this->_cookie('user_name'),604800);
			session('user_verify',$this->_cookie('user_verify'),604800);
		}
		if($this->_get('app')==1){	//切换为APP模式
			cookie('app',1,604800);
			session('app',1,604800);
		}
		if($this->_get('app')==2){
			cookie('app',2,604800);
			session('app',2,604800);
		}
	}
	
	//投标
	public function subcasts(){
		$models = new Model();
		$uid=$this->_session('user_uid');
		$uname=$this->_session('user_name');
		//解决多次提交导致的误操作
		$number=$this->orderNumber();
		$this->bidPretreatment($number);
		if($this->_post('price')<=0){
			$this->ajaxReturn(0,"投标金额有误",0);
		}
		$borrowing=D('Borrowing');
		$refund=M('collection');
		$cache = cache(array('expire'=>40));
		$models = new Model();
		$uid=$this->_post('uid')?$this->_post('uid'):$this->_session('user_uid');
		$refu=$refund->field('nper')->where('uid='.$uid.' and bid='.$this->_post('id'))->order('`nper` DESC ')->find();
		$uname=$this->_post('uname')?$this->_post('uname'):$this->_session('user_name');
			$borr=$borrowing->where('`id`='.$this->_post('id'))->find();
			$one=$borr['valid']-floor(($borr['endtime']-time())/86400);//获取第一个应扣除天数
			$counters=$this->counters($this->_post('price'),$borr['rates'],$borr['deadline'],$borr['candra'],$borr['way']);	//利息计算
			unset($counters['total']);
			unset($counters['interest']);
			foreach($counters as $id=>$coun){
				if($id==0){	//第一个月
					$o_interest+=round($coun['interest']/30*(30-$one+1),2);//第一个月实际利息
					
				}else{
					$o_interest+=$coun['interest'];
				}
			}	
			unset($counters);
			unset($one);
			if($borr['surplus']>=$this->_post('price') || $borr['surplus']<$borr['min']){	//所需金额小于投标金额
			if($borr['surplus']<$this->_post('price')){	//如果投资的金额比所需的大，那么就将投资金额改为所需金额
				$_POST['price']=$borr['surplus'];
			}
				$users=reset($this->user_details());
				if($this->_post('update_uid')==$uid){
					$this->error("不能投自己的标！");
				}else{
						if($this->_post('price')<$borr['min'] || $this->_post('price')>$borr['surplus']){
							if($borr['surplus']>$borr['min']){	//如果所需金额大于最小投资金额
								$this->ajaxReturn(0,"操作有误",0);
							}
						}
						if($this->_post('price')>$borr['max']){
							if($borr['max']>0){
								$this->ajaxReturn(0,"操作有误",0);
							}
						}
						if($this->_post('price')>$users['available_funds']){	//资金不足
							$this->ajaxReturn(0,"账户余额不足，请充值",0);
						}
						$user=D('User');
							if($borrowing->create()){	
								$data['surplus']=$borr['surplus']-$this->_post('price');	
								$borrow=$models->table('ds_borrowing')->where('id='.$this->_post('id'))->save($data);
								$rewardCalculationArr['money']			=$borr['money'];
								$rewardCalculationArr['price']			=$this->_post('price');
								$money=M('money');
								$array['type']			=1;
								$array['uid']			=$uid;
								$array['bid']			=$this->_post('id');
								$array['instructions']	='对<a href="'.$_SERVER['HTTP_REFERER'].'">【'.$borr['title'].'】</a>的投标';
								$logtotal=$moneyarr['total_money']=$array['total']			=$users['total_money']-$this->_post('price');
								$logavailable=$moneyarr['available_funds']=$array['available']		=$users['available_funds']-$this->_post('price');
								$array['interest']						=$o_interest;
								$moneyarr['stay_interest']=$array['stay_interest']=$users['stay_interest']+$array['interest'];
								$moneyarr['due_in']=$array['collected']	=$users['due_in']+$this->_post('price')+$array['interest'];
								$array['candra']		=$borr['candra'];
								$array['operation']		=$this->_post('price');
								
								$borrowlog=$this->borrowLog($array);
								$money=$models->table('ds_money')->where('uid='.$uid)->save($moneyarr);
								//记录添加点
								$userLog=$this->userLog('对【'.$borr['title'].'】的投标');//会员记录
								
								$moneyLog=$this->moneyLog(array(0,'对【'.$borr['title'].'】的投标,扣除资金',$array['operation'],'平台',$logtotal,$logavailable,$users['freeze_funds']));	//资金记录
								
								$sendMsg=$this->silSingle(array('title'=>'对【'.$borr['title'].'】的投标','sid'=>$uid,'msg'=>'对<a href="'.$_SERVER['HTTP_REFERER'].'">【'.$borr['title'].'】</a>的投标,冻结资金'));//站内信
								$arr['member']=array('uid'=>$uid,'name'=>'mem_flow');		
								//$integralAdd=$this->integralAdd($arr);	//积分操作
								//邮件通知
								$mailNotice['uid']=$uid;
								$mailNotice['title']='对【'.$borr['title'].'】的投标';
								$mailNotice['content']='
									<div style="margin: 6px 0 60px 0;">
										<p>对【'.$borr['title'].'】的投标，扣除资金：<font color="#ff0000"><b>'.$array['operation'].'元</b></font></p>
										<p><a href="http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/Loan/invest/'.$borr['id'].'.html">http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/Loan/invest/'.$borr['id'].'.html</a></p>
										<p>如果您的邮箱不支持链接点击，请将以上链接地址拷贝到你的浏览器地址栏中。</p>
									</div>
									<div style="color: #999;">
										<p>发件时间：'.date('Y/m/d H:i:s').'</p>
										<p>此邮件为系统自动发出的，请勿直接回复。</p>
									</div>';
								$this->mailNotice($mailNotice);
								
								unset($array);
								unset($moneyarr);
								unset($users);
								$users=reset($this->user_details($borr['uid']));
								$array['type']				=2;
								$array['uid']				=$borr['uid'];
								$array['uname']				=$uname;
								$array['bid']				=$this->_post('id');
								$array['instructions']		='用户：'.$uname.'对<a href="'.$_SERVER['HTTP_REFERER'].'">【'.$borr['title'].'】</a>的投标';
								$moneyarr['total_money']=$array['total']			=$users['total_money']+$this->_post('price');
								$moneyarr['available_funds']=$array['available']		=$users['available_funds']+$this->_post('price');
								$array['deadline']		=$this->_post('deadline');
								$array['candra']		=$borr['candra'];
								$array['interest']						=$o_interest;
								$moneyarr['stay_still']=$array['also']	=$users['stay_still']+$this->_post('price')+$array['interest'];
								$array['operation']		=$this->_post('price');
								$borrowlogs=$this->borrowLog($array);
								$collection=$this->icollection($this->_post('id'),$uid);//收款记录
								unset($array);
								unset($moneyarr);
								unset($users);
								if($borr['surplus']==$this->_post('price')){	//满标							
									$borrows=$models->table('ds_borrowing')->where('id='.$this->_post('id'))->save(array('state'=>1));
									$this->ajaxReturn(1,'投标成功',1);
								}else{
									$this->ajaxReturn(1,'投标成功',1);
								}
							}else{
								 $this->ajaxReturn(0,$borrowing->getError(),0);
							}
						
				}
			}else{
				$this->ajaxReturn(0,"此标状态已发生改变，请从新提交",0);
			}
	}
	
	/**
	*
	*前台退出
	*
	*/
	public function exits(){
		session('user_uid',null);
		session('user_name',null);
		session('user_verify',null);
		cookie('user_uid',null);
		cookie('user_name',null);
		cookie('user_verify',null);
		cookie('promote',null);
		cookie('user_promote',null);
		echo "<script>window.location.href='".__ROOT__."/Win/Logo/login.html';</script>";
	}
		 
	 /**
	 *
	 * @前台更新
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */	
	public function tfUpda(){
		$user=M('user');
		$users=$user->field('username,password')->where('id='.$this->_session('user_uid'))->find();
		if($this->_session('user_verify')==MD5($users['username'].DS_ENTERPRISE.$users['password'].DS_EN_ENTERPRISE)){
			$this->upda();
		}else{
			echo '非法操作，网警已介入！';
		}
	}
	
	/**
	 * @前台验证
     * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function homeVerify(){
		
		if($this->_session('user_uid')){
			$user=M('user');
			$users=D('User')->relation('userinfo')->where('id='.$this->_session('user_uid'))->find();
			
			if($this->_session('user_verify') !== MD5($users['username'].DS_ENTERPRISE.$users['password'].DS_EN_ENTERPRISE)){
				session('user_uid',null);
				session('user_name',null);
				session('user_verify',null);
				echo "<script>window.location.href='".__ROOT__."/Win/Logo/login';</script>";
				
				
			}
			
		}else{
			echo "<script>window.location.href='".__ROOT__."/Win/Logo/login';</script>";
		}
	 }
}
?>