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
class SharingAction extends Action{
	/**
	 * @前台验证
     * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function homeVerify(){
		if($this->_session('user_uid')){
			$user=M('user');
			$users=$user->field('username,password,email')->where(array('id'=>$this->_session('user_uid')))->find();
			if($this->_session('user_verify') !== MD5($users['username'].DS_ENTERPRISE.$users['password'].DS_EN_ENTERPRISE)){
				session('user_uid',null);
				session('user_name',null);
				session('user_verify',null);
				$this->error("请先重新登陆",'__ROOT__/Logo/login.html');
			}
			//if(!$users['email']){
			//	$this->error("请先通过邮箱验证",'__ROOT__/Logo/emails.html');
			//}
		}else{
			$this->error("请先登陆",'__ROOT__/Logo/login.html');
		}
	 }
	 
	/**
	  * @返回值/错误信息
	  * @in		数组
	  *
	  */
	 protected function remote($in){
		if($in['value'] == 'NO'){
			$this->error($in['error'],$in['url']);
		}else if($in['value'] == 'accredit'){
			$this->error($in['error'],$in['url']);	
		}else{
			return $in['value'];
		}	
	 }
	 
	/**
	 * @根据id生成唯一订单号
	 * @当前时间戳+随机
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function orderNumber() {
		$curlPost = "dswjw=".$_SERVER['SERVER_NAME']."&dswjn=".DS_NUMbER;
		$url='http://www.tifaweb.com/Api/Core/orderNumberApi';  
		$in=$this->Curl($curlPost,$url);
		$remote=$this->remote($in);
		if(!$remote){
			$url='http://www.dswjcms.com/Api/Core/orderNumberApi';  
			$in=$this->Curl($curlPost,$url);
			$remote=$this->remote($in);
		}
		return $remote;
	}

	/**
     * @后台操作记录
     * @type    记录类型
     * @id      是否开启
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
     */
    protected function Record($type,$id=0){
        if($id==0){
            $Operation = M('operation');
            $array['name']= $_SESSION['admin_name'];
            $array['page']= $_SERVER['PHP_SELF'];
            $array['type']= $type;
            $array['ip']= get_client_ip();
            $array['time']= time();
            $Operation->add($array);
        }
    }

	/**
	 *
	 * @城市
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */	
	protected function city(){
		$citys = F('city');  // 获取缓存
		if(!$citys){
			$city	=	M('city');
			$city=$city->select();
			foreach($city as $cy){
				$citys[$cy['var']]=$cy['city'];
			}
			F('city',$citys);	//设置缓存
		}
		return $citys;
	}
	
	/**
	 * @取前几条数据
	 * @m		传入的model
	 * @w		查询条件
	 * @o		排序
	 * @l		条数
	 * @r		是否关联查询
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function top($m,$w,$o,$l,$r=1) {
		$model=D($m);
		if($r==1){
			return $model->relation(true)->where($w)->order($o)->limit($l)->select();
		}else{
			return $model->where($w)->order($o)->limit($l)->select();
		}
		
	}
	
    /**
	 *
	 * @邮件发送
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function email_send($arr){
		$this->copyright();
		import('ORG.Custom.PhpMailer');
		$mail = new PHPMailer(); 
		$smtp			=	$arr['smtp'];
		$validation		=	$arr['validation'];
		$send_email		=	$arr['send_email'];
		$password		=	$arr['password'];
		$addresser		=	$arr['addresser'];
		$receiver_email_array  =	array_filter(explode(',',$arr['receiver_email_array']));
		$receipt_email	=  	$arr['receipt_email'];
		$title			=	$arr['title'];
		$content		=	$arr['content'];
		$addattachment	=	$arr['addattachment'];
		$ishtml			=	$arr['ishtml'];
		$mail->IsSMTP(); // 使用SMTP方式发送
		$mail->CharSet='UTF-8';// 设置邮件的字符编码
		$mail->Host = "$smtp"; // 您的企业邮局域名
		$mail->SMTPAuth = $validation==1?true:false; // 启用SMTP验证功能
		$mail->Username = "$send_email"; // 邮局用户名(请填写完整的email地址)
		$mail->Password = "$password"; // 邮局密码
		$mail->From = "$send_email"; //邮件发送者email地址
		$mail->FromName = "$addresser";	//发件人
		if($receiver_email_array){	//群发
			foreach($receiver_email_array as $rea){
				$mail->AddAddress("$rea");
			}
		}else{
			$mail->AddAddress("$receipt_email");//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
		}
		//$mail->AddReplyTo("", "");	//添加回复
		if($addattachment){
			$mail->AddAttachment("$addattachment"); // 添加附件
		}
		$mail->IsHTML($ishtml==1?true:false); // set email format to HTML //是否使用HTML格式
		$mail->Subject = "$title"; //邮件标题
		$mail->Body = "$content"; //邮件内容
		$mail->AltBody = "点石为金借贷"; //附加信息，可以省略
		if(!$mail->Send())
		{
			
			//echo '邮件发送失败. <p>错误原因: '. $mail->ErrorInfo;
			//exit;
			//如果不成功，就再次执行，直接成功为止
			$mail->Smtpclose();	//关闭
			$mail = new PHPMailer(); 
			$mail->IsSMTP(); // 使用SMTP方式发送
			$mail->CharSet='UTF-8';// 设置邮件的字符编码
			$mail->Host = "$smtp"; // 您的企业邮局域名
			$mail->SMTPAuth = $validation==1?true:false; // 启用SMTP验证功能
			$mail->Username = "$send_email"; // 邮局用户名(请填写完整的email地址)
			$mail->Password = "$password"; // 邮局密码
			$mail->From = "$send_email"; //邮件发送者email地址
			$mail->FromName = "$addresser";	//发件人
			if($receiver_email_array){	//群发
				foreach($receiver_email_array as $rea){
					$mail->AddAddress("$rea");
				}
			}else{
				$mail->AddAddress("$receipt_email");//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
			}
			//$mail->AddReplyTo("", "");	//添加回复
			if($addattachment){
				$mail->AddAttachment("$addattachment"); // 添加附件
			}
			$mail->IsHTML($ishtml==1?true:false); // set email format to HTML //是否使用HTML格式
			$mail->Subject = "$title"; //邮件标题
			$mail->Body = "$content"; //邮件内容
			$mail->AltBody = "点石为金借贷"; //附加信息，可以省略
		}
		return true;
    }
		
   /**
	*
	* @系统配置
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function systems(){
		$this->copyright();
		$sys = F('systems');  // 获取缓存
		if(!$sys){
			$system	=	M('system');
			$system=$system->select();
			foreach($system as $s){
				$sys[$s['name']]=$s['value'];
			}
			F('systems',$sys);	//设置缓存
		}
		return $sys;
	}
	
	/**
	*
	* @标操作记录
	* @id 		1多维数组0一维
	* @n		订单号
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	* @更新：2014/7/2添加$n,给记录添加了一个订单号功能，以降低大数据下的并发问题    shop猫
	*
	*/
    protected function borrowLog($arr,$id=0,$n=0){
			$models = new Model();
			$n=$n>0?$n:$this->orderNumber();
			if($id==1){
				foreach($arr as $k => $ar){
					$array[$k]['type']		= $ar['type'];
					unset($ar['type']);
					$array[$k]['actionname']= json_encode($ar);
					$array[$k]['ip']		= get_client_ip();
					$array[$k]['time']		= time();
					$array[$k]['number']	=$n;
					$array[$k]['bid']		=$ar['bid'];
					$array[$k]['uid']		=$ar['uid'];
				}
				return $models->table('ds_borrow_log')->addAll($array);
			}else{
				$array['type']		= $arr['type'];
				unset($arr['type']);
				$array['actionname']= json_encode($arr);
				$array['ip']		= get_client_ip();
				$array['time']		= time();
				$array['number']	=$n;
				$array['bid']		= $arr['bid'];
				$array['uid']		= $arr['uid'];
				return $models->table('ds_borrow_log')->add($array);
			}
			
    }
	
	/**
	*
	* @会员操作记录
	* @arr		记录说明
	* @uid		用户ID
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
    protected function userLog($arr,$uid){
			$models = new Model();
            $array['uid']		= $uid?$uid:$this->_session('user_uid');
			$array['actionname']= $arr;
			$array['page']		= $_SERVER['PHP_SELF'];
            $array['ip']		= get_client_ip();
            $array['time']		= time();
			return $models->table('ds_user_log')->add($array);
    }

	/**
     * @资金/积分操作记录
     * @array   0操作类型1操作说明2操作金额3交易对方4总额5余额6冻结7用户
	 * @array	类型细分
     * @id      是否开启
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
     */
    protected function moneyLog($array,$finetype,$id=0){
        if($id==0){
			$models = new Model();
            $arrays['uid']				= $array[7]?$array[7]:$this->_session('user_uid');
            $arrays['type']				= $array[0];
			$arrays['actionname']		= $array[1];
			$arrays['total_money']		= $array[4];
			$arrays['available_funds']	= $array[5];
			$arrays['freeze_funds']		= $array[6];
			$arrays['counterparty']		= $array[3];
			$arrays['operation']		= $array[2];
			$arrays['finetype']			= $finetype?$finetype:'1';
            $arrays['time']				= time();
			$arrays['ip']				= get_client_ip();
			return $models->table('ds_money_log')->add($arrays);
        }
    }
	
   /**	
	* @用户详情
	* @uid		传入的用户ID
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function user_details($uid=0){
		$user	=	D('User');
		$unite	=	M('unite');
		$citys=$this->city();
		$unite=$unite->field('pid,name,value')->where('`state`=0')->order('`order` asc,`id` asc')->select();
		if($uid){
			$id=$uid;
		}else{
			$id=$this->_session('user_uid');
		}
		$list = $user->relation(true)->where('id='.$id)->select();
		$integral=$list[0]['member_total_score'];
		$membership_grade=M('membership_grade');
		$grade_list = $membership_grade->where($integral.'>=min and '.$integral.'<=max')->field('name,img')->select();
		foreach($unite as $ue){
			switch($ue['pid']){
				case 8:
				if($ue['value']==$list[0]['userinfo']['education']){
					$education=$ue['name'];
				}
				break;
				case 9:
				if($ue['value']==$list[0]['userinfo']['monthly_income']){
					$monthly_income=$ue['name'];
				}
				break;
				case 10:
				if($ue['value']==$list[0]['userinfo']['housing']){
					$housing=$ue['name'];
				}
				break;
				case 11:
				if($ue['value']==$list[0]['userinfo']['buy_cars']){
					$buy_cars=$ue['name'];
				}
				break;
				case 12:
				if($ue['value']==$list[0]['userinfo']['industry']){
					$industry=$ue['name'];
				}
				break;
				case 13:
				if($ue['value']==$list[0]['userinfo']['national']){
					$national=$ue['name'];
				}
				break;
			}
		}
		$native_place=explode(" ",$list[0]['userinfo']['native_place']);
		$location=explode(" ",$list[0]['userinfo']['location']);
		$native_place=$citys[$native_place[0]]." ".$citys[$native_place[1]]." ".$citys[$native_place[2]];
		$location=$citys[$location[0]]." ".$citys[$location[1]]." ".$citys[$location[2]];
		if($list[0]['userinfo']['marriage']==1){
			$marriage="未婚";
		}else if($list[0]['userinfo']['marriage']==2){
			$marriage="已婚";
		}else{
			$marriage="保密";
		}
		$list[0]['membership_grade_name']=$grade_list[0]['name'];
		$list[0]['membership_grade_img']=$grade_list[0]['img'];
		$list[0]['name']=$list[0]['userinfo']['name'];
		$list[0]['gender']=$list[0]['userinfo']['gender']?"女":"男";
		$list[0]['national']=$national;
		$list[0]['born']=$list[0]['userinfo']['born'];
		$list[0]['idcard']=$list[0]['userinfo']['idcard'];
		$list[0]['idcard_img']=array_splice(explode(",",$list[0]['userinfo']['idcard_img']),1);;
		$list[0]['native_place']=$native_place;
		$list[0]['location']=$location;
		$list[0]['marriage']=$marriage;
		$list[0]['education']=$education;
		$list[0]['monthly_income']=$monthly_income;
		$list[0]['housing']=$housing;
		$list[0]['buy_cars']=$buy_cars;
		$list[0]['industry']=$industry;
		$list[0]['qq']=$list[0]['userinfo']['qq'];
		$list[0]['company']=$list[0]['userinfo']['company'];
		$list[0]['assure']=$list[0]['userinfo']['assure'];
		$list[0]['assurestate']=$list[0]['userinfo']['assurestate'];
		$list[0]['fixed_line']=$list[0]['userinfo']['fixed_line'];
		$list[0]['cellphone']=$list[0]['userinfo']['cellphone'];
		$list[0]['wechat']=$list[0]['userinfo']['wechat'];
		$list[0]['certification']=$list[0]['userinfo']['certification'];
		$list[0]['email_audit']=$list[0]['userinfo']['email_audit'];
		$list[0]['cellphone_audit']=$list[0]['userinfo']['cellphone_audit'];
		$list[0]['video_audit']=$list[0]['userinfo']['video_audit'];
		$list[0]['site_audit']=$list[0]['userinfo']['site_audit'];
		$list[0]['wechat_audit']=$list[0]['userinfo']['wechat_audit'];
		unset($list[0]['userinfo']);
		return $list;
    }
	
   /**
	* @认证资料
	* @id	0全部1实名2视频3现场4手机
	* @q	不为0时显示认证信息
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function audit($id=0,$q=0){
		if($id==1){
			$where="`certification`=1";
		}else if($id==2){
			$where="`video_audit`=1";
		}else if($id==3){
			$where="`site_audit`=1";
		}else if($id==4){
			$where="`cellphone_audit`=1";
		}else{
			$where='';
		}
		if($q){
			$field=",certification,email_audit,cellphone_audit,video_audit,site_audit,wechat_audit";
		}else{
			$field='';
		}
		$unite	=	M('unite');
		$userinfo	=	D('Userinfo');
		$citys=$this->city();
		$unite=$unite->field('pid,name,value')->where('`state`=0 and `pid`=13')->order('`order` asc,`id` asc')->select();
		foreach($unite as $ue){
			$unites[$ue['value']]=$ue['name'];
		}
		$userinfo=$userinfo->field('id,uid,name,gender,national,born,idcard,idcard_img,cellphone,native_place'.$field)->relation(true)->where($where)->order('`id` DESC')->select();
		
		foreach($userinfo as $id=>$ufo){
			$idcard_img=explode(",",$ufo['idcard_img']);
			$native_place=explode(" ",$ufo['native_place']);
			$native_place=$citys[$native_place[0]]." ".$citys[$native_place[1]]." ".$citys[$native_place[2]];
			$userinfo[$id]['native_place']=$native_place;
			$userinfo[$id]['idcard_img']=$idcard_img;
			$userinfo[$id]['national']=$unites[$ufo['national']];
			$userinfo[$id]['gender']=$ufo['gender']?"女":"男";
			$userinfo[$id]['cellphone']=$ufo['cellphone'];
			unset($userinfo[$id]['join_date']);
		}	
		return $userinfo;
    }
   
   /**
	* @用户信息表
	* @uid		用户id
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function userinfo($uid=0,$conditions=0){
		$userinfo=D('Userinfo');
		if($uid){
			if($conditions){
				$userinfos = reset($userinfo->relation(true)->field($conditions)->where("`uid`=".$uid)->select());
			}else{
				$userinfos = reset($userinfo->relation(true)->where("`uid`=".$uid)->select());
			}
		}
		
		return $userinfos;
	}
	
   /**
	*
	* @线下银行
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function offlineBank(){
		$unite=M('unite');
		$offline=M('offline');
		$list=$unite->field('name,value')->where('`state`=0 and `pid`=14')->order('`order` asc,`id` asc')->select();
		$audit=$offline->order('`id` DESC')->select();
		foreach($list as $lt){
			$userinfos[$lt['value']]=$lt['name'];
		}
		foreach($audit as $id=>$au){
			$audit[$id]['type_name']=$userinfos[$au['type']];
		}
		return $audit;
	}	
	
   /**
	* @提现手续费
	* @m	提现金额
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function withdrawalPoundage($m=0){
		$systems=$this->systems();
		if($m>0){
			if($m<=$systems['sys_wFPoundage']){	//小于免费提现额度
				$wfp=0;
			}else{	//提现手续费=（提现金额-免费额度）*提现手续费率
				$wfp=round(($m-$systems['sys_wFPoundage'])*$systems['sys_withdrawalPoundage'],2);
			}
		}
		return $wfp;
	}
	
   /**	
	* @提现用户详细
	* @id		查询id
	* @uid		用户id
	* @where	条件
	*
	*/
	protected function showUser($id=0,$uid=0,$where){
		$withdrawal=D('Withdrawal');
		$unite=M('unite');
		
		$list=$unite->field('name,value')->where('`state`=0 and `pid`=14')->order('`order` asc,`id` asc')->select();
		foreach($list as $lt){
			$unites[$lt['value']]=$lt['name'];
		}
		unset($list);
		if($id){	//单记录
			$withdrawals=reset($withdrawal->where('id='.$id)->order('`time` DESC,`id` DESC')->select());
			$userinfo=$this->userinfo($withdrawals['uid'],'uid,name,bank,bank_name,bank_account');
			$withdrawals['name']				=	$userinfo['name'];
			$withdrawals['username']			=	$userinfo['username'];
			$withdrawals['bank']				=	$unites[$userinfo['bank']];
			$withdrawals['bank_name']			=	$userinfo['bank_name'];
			$withdrawals['bank_account']		=	$userinfo['bank_account'];
			$withdrawals['withdrawal_poundage']	=	$this->withdrawalPoundage($withdrawals['money']);
			$withdrawals['account']				=	$withdrawals['money']-$this->withdrawalPoundage($ws['money']);
		}else{
			if($uid>0){	//单个用户
				$withdrawals=$withdrawal->where('uid='.$uid)->order('`time` DESC,`id` DESC')->select();
				foreach($withdrawals as $id=>$ws){
					$userinfo=$this->userinfo($ws['uid'],'uid,name,bank,bank_name,bank_account');
					$withdrawals[$id]['name']					=	$userinfo['name'];
					$withdrawals[$id]['username']				=	$userinfo['username'];
					$withdrawals[$id]['bank']					=	$unites[$userinfo['bank']];
					$withdrawals[$id]['bank_name']				=	$userinfo['bank_name'];
					$withdrawals[$id]['bank_account']			=	$userinfo['bank_account'];
					$withdrawals[$id]['withdrawal_poundage']	=	$this->withdrawalPoundage($ws['money']);
					$withdrawals[$id]['account']				=	$ws['money']-$this->withdrawalPoundage($ws['money']);
				}
			}else{	//所有用户信息
				$withdrawals=$withdrawal->where($where)->order('`time` DESC,`id` DESC')->select();
				foreach($withdrawals as $id=>$ws){
					$userinfo=$this->userinfo($ws['uid'],'uid,name,bank,bank_name,bank_account');
					$withdrawals[$id]['name']					=	$userinfo['name'];
					$withdrawals[$id]['username']				=	$userinfo['username'];
					$withdrawals[$id]['bank']					=	$unites[$userinfo['bank']];
					$withdrawals[$id]['bank_name']				=	$userinfo['bank_name'];
					$withdrawals[$id]['bank_account']			=	$userinfo['bank_account'];
					$withdrawals[$id]['withdrawal_poundage']	=	$this->withdrawalPoundage($ws['money']);
					$withdrawals[$id]['account']				=	$ws['money']-$this->withdrawalPoundage($ws['money']);
				}
			}
		}
		return $withdrawals;
    }
	
   /**
	* @充值用户详细
	* @id		查询id
	* @uid		用户id
	* @where	条件
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com	
	*
	*/
	protected function rechargeUser($id=0,$uid=0,$where){
		$recharge=D('Recharge');
		$unite=M('unite');
		$list=$unite->field('pid,name,value')->where('(`pid` = 14 or `pid` = 15 ) and `state`=0')->order('`order` asc,`id` asc')->select();
		foreach($list as $lt){
			if($lt['pid']==15){
				$online[$lt['value']]=$lt['name'];	//网上
			}else{
				$unites[$lt['value']]=$lt['name'];	//线下
			}
		}
		unset($list);
		$offline=$this->offlineBank();
		foreach($offline as $of){
			$offlin[$of['id']]=$of;
		}
		if($id){	//单记录
			$recharges=reset($recharge->relation(true)->where('id='.$id)->order('`time` DESC,`id` DESC')->select());
			$recharges['genre_name']			=	$online[$recharges['genre']];
			$recharges['oid_array']				=	$offlin[$recharges['oid']];
		}else{
			if($uid>0){	//单个用户
				$recharges=$recharge->relation(true)->where('uid='.$uid)->order('`time` DESC,`id` DESC')->select();
				foreach($recharges as $id=>$ws){
					$recharges[$id]['genre_name']			=	$online[$ws['genre']];
				}
			}else{	//所有用户信息
				$recharges=$recharge->relation(true)->where($where)->order('`time` DESC,`id` DESC')->select();
				foreach($recharges as $id=>$ws){
					$recharges[$id]['genre_name']			=	$online[$ws['genre']];
					$recharges[$id]['oid_name']				=	$offlin[$ws['oid']]['bank'];
				}
			}
		}
		return $recharges;
    }
   /**
    * @充值手续费
	* @m	充值金额
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function topUpFees($m=0){
		$systems=$this->systems();
		if($m>0){
			if($systems['sys_topUFC']==0){	//大于免费额度收取手续费
				if($m<=$systems['sys_rechargeFA']){	//小于免费提现额度
					$wfp=0;
				}else{	//充值手续费=（充值金额-免费额度）*充值手续费
					$wfp=round(($m-$systems['sys_rechargeFA'])*$systems['sys_topUpFees'],2);
				}
			}else if($systems['sys_topUFC']==1){	//小于免费额度收取手续费
				if($m<=$systems['sys_rechargeFA']){	//小于免费提现额度
					//充值手续费=（充值金额-免费额度）*充值手续费率
					$wfp=round(($m-$systems['sys_rechargeFA'])*$systems['sys_topUpFees'],2);;
				}else{
					$wfp=0;
				}
			}
		}
		return $wfp;
	}
	
	/**
    * @线上充值手续费
	* @m	充值金额
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function onlineUpFees($m=0){
		$systems=$this->systems();
		if($m>0){
			//充值手续费=充值金额*充值手续费
			$wfp=round($m*$systems['sys_onlinePoundage'],2);
		}
		return $wfp;
	}

   /**
	*
	* @联动（发标所需联动）
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function borrowLinkage(){
		$unite=M('unite');
		$list=$unite->where('`state`=0 and `pid`<8')->order('`order` asc,`id` asc')->select();
		foreach($list as $lt){
			switch($lt['pid']){
				case 1:
				$linkage['privacy'][]=$lt;
				break;
				case 2:
				$linkage['deadline_month'][]=$lt;
				break;
				case 3:
				$linkage['deadline_day'][]=$lt;
				break;
				case 4:
				$linkage['way'][]=$lt;
				break;
				case 5:
				$linkage['valid'][]=$lt;
				break;
				case 6:
				$linkage['min'][]=$lt;
				break;
				case 7:
				$linkage['max'][]=$lt;
				break;
			}
		}
		return $linkage;
	}
	/**
	 * @借款单条
	 * @id		传入的的借款ID
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 *
	 */
	protected function borrow_information($id){
		$borrow=$this->borrow_unicom($id);
		$bid_records=$this->bidRecords('2',$id);
		$borrow[0]['bid_records']=$bid_records;
		$bid_records_count=count($bid_records);	//投标记录
		$borrow[0]['ratio']=sprintf("%01.2f",($borrow[0]['money']-$borrow[0]['surplus'])/$borrow[0]['money']*100);	//进度
		unset($bid_records);
		unset($assure_records);
		unset($user);
		return $borrow;
	}

   /**
	* @借款信息
	* @id		单条借款传入ID
	* @where	条件
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function borrow_unicom($id=0,$where){
		$unite=M('unite');
		$list=$unite->where('`state`=0 and `pid`<8')->order('`order` asc,`id` asc')->select();
		foreach($list as $lt){
			switch($lt['pid']){
				case 1:
				$use[$lt['value']]=$lt['name'];
				break;
				case 2:
				$deadline_month[$lt['value']]=$lt['name'];
				break;
				case 3:
				$deadline_day[$lt['value']]=$lt['name'];
				break;
				case 4:
				$way[$lt['value']]=$lt['name'];
				break;
				case 5:
				$valid[$lt['value']]=$lt['name'];
				break;
				case 6:
				$min[$lt['value']]=$lt['name'];
				break;
				case 7:
				$max[$lt['value']]=$lt['name'];
				break;
			}
		}
		$Borrowing = D('Borrowing');
		if($id>0){
			$borrow = $Borrowing->relation(true)->where('id='.$id)->order('`id` DESC')->select();
		}else{
			if(isset($where)){
				$borrow = $Borrowing->relation(true)->where($where)->order('`id` DESC')->select();
			}else{
				$borrow = $Borrowing->relation(true)->order('`id` DESC')->select();
			}
		}
		foreach($borrow as $id=>$lt){
			$borrow[$id]['use']=$use[$lt['use']];
			$borrow[$id]['deadlines']=$borrow[$id]['deadline'];
			$borrow[$id]['deadlines']=$deadline_month[$lt['deadline']];
			$borrow[$id]['flow_deadlines']=$deadline_month[$lt['flow_deadline']];
			$borrow[$id]['min_limits']=$deadline_month[$lt['min_limit']];
			$img=array_splice(explode(",",$lt['data']),1);
			$borrow[$id]['way']=$way[$lt['way']];
			$borrow[$id]['valids']=$borrow[$id]['valid'];
			$borrow[$id]['valid']=$valid[$lt['valid']];
			$borrow[$id]['min_name']=$min[$lt['min']];
			$borrow[$id]['max_name']=$max[$lt['max']]?$max[$lt['max']]:"无限制";
			$borrow[$id]['img']=$img[0];
			$borrow[$id]['already']=($lt['money']-$lt['surplus'])?($lt['money']-$lt['surplus']):'0.00';
			$borrow[$id]['alreadys']=($lt['money']-$lt['assure'])?($lt['money']-$lt['assure']):'0.00';
			$borrow[$id]['ratio']=sprintf("%01.2f",($lt['money']-$lt['surplus'])/$lt['money']*100);
			$borrow[$id]['ratios']=sprintf("%01.2f",($lt['money']-$lt['assure'])/$lt['money']*100);
			$Guarantee=D('Guarantee');
			$guaranteecomp=M('guaranteecomp');
			$borrow[$id]['guara']=$Guarantee->where('bid='.$lt['id'])->relation(true)->find();
			$borrow[$id]['guara']['gcompanys']=$guaranteecomp->field('name')->where('id='.$borrow[$id]['guara']['gcompany'])->find();		
			
			switch($lt['state']){
					case 0:
					$borrow[$id]['state_name']="正在投标";
					break;
					case 1:
					$borrow[$id]['state_name']="还款中";
					break;
					case 2:
					$borrow[$id]['state_name']="已完成";
					break;
			}
		}
		return $borrow;
	}
	
   /**
	* @单用户借款信息
	* @uid		传入用户ID
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function borrowUidUnicom($uid){
		$unite=M('unite');
		$list=$unite->where('`state`=0 and `pid`<8')->order('`order` asc,`id` asc')->select();
		foreach($list as $lt){
			switch($lt['pid']){
				case 1:
				$use[$lt['value']]=$lt['name'];
				break;
				case 2:
				$deadline_month[$lt['value']]=$lt['name'];
				break;
				case 3:
				$deadline_day[$lt['value']]=$lt['name'];
				break;
				case 4:
				$way[$lt['value']]=$lt['name'];
				break;
				case 5:
				$valid[$lt['value']]=$lt['name'];
				break;
				case 6:
				$min[$lt['value']]=$lt['name'];
				break;
				case 7:
				$max[$lt['value']]=$lt['name'];
				break;
			}
		}
		$Borrowing = D('Borrowing');
		if($uid>0){
			$borrow = $Borrowing->relation(true)->where('uid='.$uid)->select();
		}else{
			if(isset($state)){
				$borrow = $Borrowing->relation(true)->where('state='.$state)->select();
			}else{
				$borrow = $Borrowing->relation(true)->select();
			}
		}
		foreach($borrow as $id=>$lt){
			$borrow[$id]['use']=$use[$lt['use']];
			$borrow[$id]['deadlines']=$borrow[$id]['deadline'];
			if($lt['candra']==0){
				$borrow[$id]['deadline']=$deadline_month[$lt['deadline']];
			}else{
				$borrow[$id]['deadline']=$deadline_day[$lt['deadline']];
			}
			$img=array_splice(explode(",",$lt['data']),1);
			$borrow[$id]['way']=$way[$lt['way']];
			$borrow[$id]['valid']=$valid[$lt['valid']];
			$borrow[$id]['min_name']=$min[$lt['min']];
			$borrow[$id]['max_name']=$max[$lt['max']]?$max[$lt['max']]:"无限制";
			$borrow[$id]['img']=$img[0];
			$borrow[$id]['already']=($lt['money']-$lt['surplus'])?($lt['money']-$lt['surplus']):'0.00';
			$borrow[$id]['alreadys']=($lt['money']-$lt['assure'])?($lt['money']-$lt['assure']):'0.00';
			$borrow[$id]['ratio']=sprintf("%01.2f",($borrow[$id]['money']-$borrow[$id]['surplus'])/$borrow[$id]['money']*100);	//标进度
			$borrow[$id]['ratios']=sprintf("%01.2f",($borrow[$id]['money']-$borrow[$id]['assure'])/$borrow[$id]['money']*100);	//担保进度
			$flow_total=floor($borrow[$id]['money']/$borrow[$id]['min']);	//流转总份数
			$borrow[$id]['subscribe']=$flow_total-$borrow[$id]['flows'];	//可认购数
			$borrow[$id]['flow_ratio']=sprintf("%01.2f",$borrow[$id]['flows']/$flow_total*100);	//流转标进度
			
				switch($lt['state']){
					case 0:
					$borrow[$id]['state_name']="投标中";
					break;
					case 1:
					$borrow[$id]['state_name']="还款中";
					$borrow[$id]['state_names']="正在还款";
					break;
					case 2:
					$borrow[$id]['state_names']=$borrow[$id]['state_name']="已完成";
					break;
					
					break;
				}
		}
		return $borrow;
	}
	/**
	 * @借款信息(详细)	
	 * @where	条件
	 * @limit	LIMIT 
	 * @order	排序
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 *
	 */
	protected function borrow_unicoms($where,$limit,$order){
		if(!isset($where)){
			$where="`id`>0";
		}
		$unite=M('unite');
		$list=$unite->where('`state`=0 and `pid`<8')->order('`order` asc,`id` asc')->select();
		foreach($list as $lt){
			switch($lt['pid']){
				case 1:
				$use[$lt['value']]=$lt['name'];
				break;
				case 2:
				$deadline_month[$lt['value']]=$lt['name'];
				break;
				case 3:
				$deadline_day[$lt['value']]=$lt['name'];
				break;
				case 4:
				$way[$lt['value']]=$lt['name'];
				break;
				case 5:
				$valid[$lt['value']]=$lt['name'];
				break;
				case 6:
				$min[$lt['value']]=$lt['name'];
				break;
				case 7:
				$max[$lt['value']]=$lt['name'];
				break;
			}
		}
		
		$Borrowing = D('Borrowing');
		$borrow = $Borrowing->where($where)->order($order)->limit($limit)->select();
		if($borrow){
		foreach($borrow as $id=>$lt){
			$bid_records=$this->bidRecords('8',$lt['id']);
			$bid_records_count=count($bid_records);	//投标记录
			unset($bid_records);
			$borrow[$id]['privacy']=$use[$lt['privacy']];
			$borrow[$id]['deadlines']=$deadline_month[$lt['deadline']];
			$borrow[$id]['flow_deadlines']=$deadline_month[$lt['flow_deadline']];
			$borrow[$id]['min_limits']=$deadline_month[$lt['min_limit']];
			$img=explode(";",$lt['data']);
			$img=explode(",",$img[0]);
			
			$borrow[$id]['way']=$way[$lt['way']];
			$borrow[$id]['valid']=$valid[$lt['valid']];
			$borrow[$id]['min']=$min[$lt['min']];
			$borrow[$id]['max']=$max[$lt['max']]?$max[$lt['max']]:"无限制";
			$borrow[$id]['img']=$img[0];
			$borrow[$id]['bid_records_count']=$bid_records_count;
			$borrow[$id]['ratio']=sprintf("%01.2f",($borrow[$id]['money']-$borrow[$id]['surplus'])/$borrow[$id]['money']*100);	//进度
			
			switch($lt['state']){
					case 0:
					$borrow[$id]['state_name']="正在投标";
					break;
					case 1:
					$borrow[$id]['state_name']="还款中";
					break;
					case 2:
					$borrow[$id]['state_name']="已完成";
					break;
			}
			
			unset($borrow[$id]['password']);
			unset($borrow[$id]['data']);
			unset($borrow[$id]['content']);
			unset($borrow[$id]['time']);
			unset($borrow[$id]['join_date']);
			unset($borrow[$id]['member_total_score']);
		}
		}
		
		return $borrow;
	}
	
	/**
	*
	* @投标记录(完整版)
	* @bid		标ID
	* @limit	条数
	* @order	排序
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function bRecord($bid=0,$limit,$order){	
		$order=$order?$order:'`id` desc';
        $user_log=M('borrow_log');
		$user_log=$user_log->limit($limit)->order($order)->select();
		if($user_log){
			foreach($user_log as $id=>$ulog){
				$user_log[$id]['actionname']=$actionname=json_decode($ulog['actionname'], true);
				if($actionname['bid'] !==$bid && $bid>0){
					unset($user_log[$id]);
				}						
			}
		}
		return $user_log;					
    }
	
   /**
	*
	* @投标记录
	* @type		记录状态
	* @bid		标ID
	* @uid		用户ID
	* @limit	limit
	* @state	详细
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function bidRecords($type,$bid=0,$uid=0,$limit){	
        $user_log=M('borrow_log');
			if($type){
				$bids=$bid>0?" and `bid`=".$bid:'';
				$uids=$uid>0?" and `uid`=".$uid:'';
					$user_log=$user_log->where('`type`='.$type.$bids.$uids)->order('`time` DESC ')->limit($limit)->select();
				if(!$user_log){
					return array();
				}
				
				foreach($user_log as $id=>$ulog){
					$user_log[$id]['actionname']=json_decode($ulog['actionname'], true);
					
					if($bid==0){
						
							$user_log[$id]['details']=reset($this->borrow_unicom($ulog['bid']));			//标详情
							
							//获取收款状态，如果没有待收的添加状态
							$coll=M('collection')->where('`bid`='.$ulog['bid'].' and `uid`='.$uid.' and `type`=1')->count();
							$assignment=M('assignment')->where('`bid`='.$ulog['bid'].' and `uid`='.$uid.' and `type` != 2')->find();
							if($assignment){
								if($coll>0 && $assignment['surplus']<=0){	//只有在有已还时才进入筛选,债权转被认购完
									$colls=M('collection')->where('`bid`='.$ulog['bid'].' and `uid`='.$uid.' and `type`=0')->count();
									if($colls<1){
									$user_log[$id]['collection']=1;
									}
								}
							}
							$u_log[]=$user_log[$id];
							
					}else{
						$u_log[]=$user_log[$id];
					}
				}
				return $u_log;
			}
    }
	
   /**
	*
	* @投标处理-借款人信息
	* @field	标ID
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function borr($id){
		if(!$id){
			return false;
		}
		$borrow=$this->borrow_information($id);
		$borr=array(
					'id'			=>$borrow[0]['id'],
					'surplus'		=>$borrow[0]['surplus'],
					'assure'		=>$borrow[0]['assure'],
					'uid'			=>$borrow[0]['uid'],
					'type'			=>$borrow[0]['type'],
					'title'			=>$borrow[0]['title'],
					'password'		=>$borrow[0]['password'],
					'min'			=>$borrow[0]['min'],
					'max'			=>$borrow[0]['max'],
					'total_money'	=>$borrow[0]['total_money'],
					'available_funds'=>$borrow[0]['available_funds'],
					'freeze_funds'	=>$borrow[0]['freeze_funds'],
					'vip_audit'		=>$borrow[0]['vip_audit'],
					'candra'		=>$borrow[0]['candra'],
					'deadline'		=>$borrow[0]['deadlines'],
					'deadlinea'		=>$borrow[0]['deadline'],
					'money'			=>$borrow[0]['money'],
					'assures'		=>$borrow[0]['assures'],
					'rates'			=>$borrow[0]['rates'],
					'username'		=>$borrow[0]['username'],
					'valid'			=>$borrow[0]['valid'],
					'valids'		=>$borrow[0]['valids'],
					'endtime'		=>$borrow[0]['endtime'],
					);
		unset($borrow);
		return $borr;
	}
	
   /**
	*
	* @资金表
	* @field		需要的字段
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function moneys($field){
		$money=M('money');
		$money=$money->field($field)->select();

		if(!$money){
			$this->error("请提交正确的field，如没有可为空！");
		}
		foreach($money as $my){
			$moneys[$my['uid']]=$my;
		}
		return $moneys;
	}
	
   /**
	*
	* @资金单条记录
	* @uid		用户id
	* @field	需要的字段
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function moneySingle($uid,$field){
		$money=$this->moneys($field);
		return $money[$uid];
	}
	
	/**
	 *
	 * @资金记录
	 * @uid			用户ID
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 *
	 */
	protected function moneyRecord($uid){
		$uids=$uid?' and uid='.$uid:'';
		$money_log=D('Money_log');
		$list=$money_log->relation(true)->where('type=0'.$uids)->order('time DESC,id DESC ')->select();	//资金使用记录
		return $list;
		
	}
	
	 /**
	 * @还款计划
	 * @id		标ID
	 * @--------------
	 * @nper	期数
	 * @uid		会员
	 * @bid		标ID
	 * @money	资金
	 * @time	时间
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	 protected function irefunds($id){
		$models = new Model();
		$borrowing=M('borrowing');
		$refund=M('refund');
		$borrow=$borrowing->where('id='.$id)->find();
		$counters=$this->counters($borrow['money'],$borrow['rates'],$borrow['deadline'],$borrow['candra'],$borrow['way']);	//普通标还款计划
		if($borrow['candra']==0){	//如果是月标循环还款计划
			for($i=1;$i<=$borrow['deadline'];$i++){	//换算出每月还款时间
				$time[]=$this->endMonth($i);
			}
			unset($counters['total']);
			unset($counters['interest']);
			foreach($counters as $id=>$coun){
				$refun[$id]['nper']=$id+1;
				$refun[$id]['bid']=$borrow['id'];
				$refun[$id]['money']=$coun['refund'];
				$refun[$id]['interest']=$coun['interest'];
				$refun[$id]['time']=$time[$id];
			}	
			return $models->table('ds_refund')->addAll($refun);
		}
	 }
	 
	 /**
	 * @还款计划更新
	 * @id		标ID
	 * @--------------
	 * @nper	期数
	 * @uid		会员
	 * @bid		标ID
	 * @money	资金
	 * @time	时间
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	 protected function irefundsEdit($id=1){
		$models = new Model();
		$borrowing=M('borrowing');
		$refund=M('refund');
		$borrow=$borrowing->where('id='.$id)->find();
		$money=$borrow['money']-$borrow['surplus'];
		$refun=$refund->where('bid='.$id)->select();	//获取还款计划
		$counters=$this->counters($money,$borrow['rates'],$borrow['deadline'],$borrow['candra'],$borrow['way']);	//普通标还款计划
		if($borrow['candra']==0){	//如果是月标循环还款计划
			for($i=1;$i<=$borrow['deadline'];$i++){	//换算出每月还款时间
				$time[]=$this->endMonth($i);
			}
			unset($counters['total']);
			unset($counters['interest']);
			foreach($refun as $id=>$coun){
				$refun['money']=$counters[$id]['refund'];
				$refun['interest']=$counters[$id]['interest'];
				return $models->table('ds_refund')->where('id='.$coun['id'])->save($refun);
			}			
		}else{	//如果是天标，直接显示最终还款计划
				$refun['money']=$counters['total'];
				$refun['interest']=$counters['interest'];
				return $models->table('ds_refund')->where('id='.$refun[0]['id'])->save($refun);
		}
	 }
	
	/**
	 * @收款计划
	 * @id		标ID
	 * @--------------
	 * @nper	期数
	 * @uid		会员
	 * @bid		标ID
	 * @money	资金
	 * @time	时间
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	 protected function icollection($id=4,$uid=1){
		$borrowing=M('borrowing');
		$collection=M('collection');
		$borrow=$borrowing->where('id='.$id)->find();
		$bid_record=$this->specifyUser('1',$id,$uid);
		$refund=M('refund');
		$r=$refund->field('time')->where('bid='.$id)->select();	//获取还款时间
		$one=$borrow['valid']-floor(($borrow['endtime']-time())/86400);//获取第一个应扣除天数
		
		foreach($bid_record as $record){
			$counters=$this->counters($record['total'],$borrow['rates'],$borrow['deadline'],$borrow['candra'],$borrow['way']);	//普通标还款计划
				
				unset($counters['total']);
				unset($counters['interest']);
				foreach($counters as $id=>$coun){
					$refun[$id]['nper']=$id+1;
					$refun[$id]['uid']=$uid;
					$refun[$id]['bid']=$borrow['id'];
					if($id==0){	//第一个月
						$one_rates=round($coun['interest']/30*(30-$one+1),2);//第一个月实际利息
						if(count($counters)==1){	//如果只有一个月
							$refun[$id]['money']=$one_rates+$coun['capital'];
						}else{
							$refun[$id]['money']=$one_rates;
						}
						$refun[$id]['interest']=$one_rates;
					}else{
						$refun[$id]['money']=$coun['refund'];
						$refun[$id]['interest']=$coun['interest'];
					}
					$refun[$id]['time']=$r[$id]['time'];
					
				}	
				$collection->addAll($refun);
		}
	 }
	
	/**
	 *
	 * @判断是否为周末，是则延长至星期一
	 * @str		传入时间
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 *
	 */
	protected function week($str){
		if(date('w',$str)==6){	//如果是星期六
			return $str+172800;	//加2天
		}else if(date('w',$str) == 0){	//如果是星期天
			return $str+86400;	//加1天
		}else{
			return $str;
		}
	}
	
	/**
	 *
	 * @计算正确时间
	 * @interval间隔
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 *
	 */
	 protected function endMonth($interval){
		//$firstday = date("Y-m-01",$time);
		$time=time();
		$firstday = strtotime("+$interval month");//下N个月
		$thismonth=date("m",$time);//本月
		$nextmonth=date("m",$firstday);//下N个月
		$poor=$nextmonth-$thismonth;
		$poor=$poor>0?$poor:($poor+12);//如果是正数直接显示，不是则加12
		if($poor !== $interval){	//下N个月减本月不等于间隔时间
			$firstday = strtotime(date("Y-m-01",$firstday))-3600;
			$t=$this->week($firstday);
		}else{
			$t=$this->week($firstday);
		}
		return $t;
		//echo date("Y-m-d H:i:s",$t);
		//exit;
	 }
	 
	/**
	 *
	 * @利息计算器
	 * @a		贷款本金
	 * @i		贷款年利率
	 * @n		贷款期限
	 * @u		0月1天
	 * @w		0月付息到期还本1月付本息2等额本金3等额本息
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 *
	 */
	protected function counters($a,$i,$n,$u=0,$w=0){	
		$curlPost = "dswjw=".$_SERVER['SERVER_NAME']."&dswjn=".DS_NUMbER."&dsa=".$a."&dsi=".$i."&dsn=".$n."&dsu=".$u."&dsw=".$w;
		$url='http://www.tifaweb.com/Api/Core/countersApi';  
		$in=$this->Curl($curlPost,$url);
		$remote=$this->remote($in);
		if(!$remote){
			$url='http://www.dswjcms.com/Api/Core/countersApi';  
			$in=$this->Curl($curlPost,$url);
			$remote=$this->remote($in);
		}
		return $remote;
	}
	
   /**
	*
	* @获取用户记录表对应类型的数据
	* @mid		用户记录类型
	* @id		标ID
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function specifyUser($mid,$id=0,$uid=0){
		$bid_records=$this->bidRecords($mid,$id,$uid);
		foreach($bid_records as $bds){
			if($bid_record[$bds['actionname']['uid']]){
				$bid_record[$bds['actionname']['uid']]['money']=$this->moneySingle($bds['actionname']['uid'],'uid,total_money,available_funds,freeze_funds,due_in,stay_still,stay_interest,make_interest,make_reward');
				$bid_record[$bds['actionname']['uid']]['total']=$bds['actionname']['operation']+$bid_record[$bds['actionname']['uid']]['total'];
				$bid_record[$bds['actionname']['uid']]['interest']=$bds['actionname']['interest']+$bid_record[$bds['actionname']['uid']]['interest'];
			}else{
				$bid_record[$bds['actionname']['uid']]['id']=$bds['actionname']['uid'];
				$bid_record[$bds['actionname']['uid']]['money']=$this->moneySingle($bds['actionname']['uid'],'uid,total_money,available_funds,freeze_funds,due_in,stay_still,stay_interest,make_interest,make_reward');
				$bid_record[$bds['actionname']['uid']]['total']=$bds['actionname']['operation'];
				$bid_record[$bds['actionname']['uid']]['interest']=$bds['actionname']['interest'];
			}
		}
		return $bid_record;
	}
	
   /**
	* 
	* @获取用户出借记录（用于协议书）
	* @mid		用户记录类型
	* @id		标ID
	* @fd       不合并
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function lendUser($mid,$id=0,$fd=0){
		$bid_records=$this->bidRecords($mid,$id);
		$user=M('user');
		foreach($bid_records as $id=> $bds){
			if($fd==1){//不合并
				$cand=$bds['actionname']['candra']?'天':'个月';
				$bid_record[$id]['deadline']=$bds['actionname']['deadline'].$cand;
				$bid_record[$id]['id']=$bds['actionname']['uid'];
				$bid_record[$id]['username']=reset($user->field('username')->where('id='.$bds['actionname']['uid'])->find());
				$bid_record[$id]['total']=$bds['actionname']['operation'];
				$bid_record[$id]['totals']=$bds['actionname']['operation']+$bds['actionname']['interest'];
			}else{
				if($bid_record[$bds['actionname']['uid']]){
					$bid_record[$bds['actionname']['uid']]['total']=$bds['actionname']['operation']+$bid_record[$bds['actionname']['uid']]['total'];
					$bid_record[$bds['actionname']['uid']]['totals']=$bds['actionname']['operation']+$bid_record[$bds['actionname']['uid']]['totals']+$bds['actionname']['interest'];
				}else{
					$cand=$bds['actionname']['candra']?'天':'个月';
					$bid_record[$bds['actionname']['uid']]['deadline']=$bds['actionname']['deadline'].$cand;
					$bid_record[$bds['actionname']['uid']]['id']=$bds['actionname']['uid'];
					$bid_record[$bds['actionname']['uid']]['username']=reset($user->field('username')->where('id='.$bds['actionname']['uid'])->find());
					$bid_record[$bds['actionname']['uid']]['total']=$bds['actionname']['operation'];
					$bid_record[$bds['actionname']['uid']]['totals']=$bds['actionname']['operation']+$bds['actionname']['interest'];
				}
			}
		}
		return $bid_record;
	}
	
	/**
	* @用户手动还款
	* @bid		标ID
	* @id		期数
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	public function repayment($bid,$id){
		$models = new Model();
		$refund=M('refund');
		$money=M('money');
		$borrowing=D('Borrowing');
		$collection=M('collection');
		$refun=$refund->where('bid='.$bid.' and nper='.$id)->find();
		$end_refun=$refund->where('bid='.$bid.' and nper='.($id+1))->find();//查看借款是否为最后一期
		$borr=$borrowing->relation(true)->field('uid,title,money,type')->where('id='.$bid)->find();
		$colle=$collection->where('bid='.$bid.' and nper='.$id)->select();
		//还款状态更新
		$refund->where('bid='.$bid.' and nper='.$id)->save(array('type'=>1));			
		/*投资者操作*/
		foreach($colle as $co){
			//还款状态更新
			$collection->where('bid='.$bid.' and nper='.$id.' and uid='.$co['uid'])->save(array('type'=>1));
			//增加用户资金
			$models->query("UPDATE `ds_money` SET `total_money` = total_money+".$co['money'].",`available_funds` = available_funds+".$co['money'].",`stay_interest` = stay_interest-".$co['interest'].",`make_interest` = make_interest+".$co['interest'].",`due_in` = due_in-".$co['money']." WHERE `uid` =".$co['uid']);
			$total=$money->field('total_money,available_funds,freeze_funds')->where('uid='.$co['uid'])->find();	//查询资金
			//记录添加点
			$moneyLog=$this->moneyLog(array(0,'【'.$borr['title'].'】第'.$id.'期收款',$co['money'],'平台',$total['total_money'],$total['available_funds'],$total['freeze_funds'],$co['uid']));//资金记录
			$sendMsg=$msgTools->sendMsg(3,'对【'.$borr['title'].'】第'.$id.'期收款','<a href="'.__ROOT__.'/Loan/invest/'.$bid.'.html">【'.$borr['title'].'】</a>第'.$id.'期成功收款','admin',$co['uid']);//站内信
			$this->silSingle(array('title'=>'对【'.$borr['title'].'】第'.$id.'期收款','sid'=>$co['uid'],'msg'=>'<a href="'.__ROOT__.'/Loan/invest/'.$bid.'.html">【'.$borr['title'].'】</a>第'.$id.'期成功收款'));//站内信
			//邮件通知
			$mailNotice['uid']=$co['uid'];
			$mailNotice['title']='对【'.$borr['title'].'】的第'.$id.'期还款';
			$mailNotice['content']='
				<div style="margin: 6px 0 60px 0;">
					<p>对【'.$borr['title'].'】的第'.$id.'期还款成功,收款:<font color="#ff0000"><b>'.$co['money'].'元</b></font></p>
					<p><a href="http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/Loan/invest/'.$bid.'.html">http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/Loan/invest/'.$bid.'.html</a></p>
					<p>如果您的邮箱不支持链接点击，请将以上链接地址拷贝到你的浏览器地址栏中。</p>
				</div>
				<div style="color: #999;">
					<p>发件时间：'.date('Y/m/d H:i:s').'</p>
					<p>此邮件为系统自动发出的，请勿直接回复。</p>
				</div>';
			$this->mailNotice($mailNotice);
		}
		if(!$end_refun){	//已还完
			$borrowing->where('id='.$bid)->save(array('state'=>2));
		}
		echo '<p class="green">还款成功</p>';
		echo '<p class="jump">
		页面自动 <a href="#">跳转</a> 等待时间： <b>3秒</b>
		</p>';

	}	
	
	/**
	*
	* @excel列转换
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function letter() {
		return $array=array(0=>'A',1=>'B',2=>'C',3=>'D',4=>'E',5=>'F',6=>'G',7=>'H',8=>'I',9=>'J',10=>'K',11=>'L',12=>'M',13=>'N',14=>'O',15=>'P',16=>'Q',17=>'R',18=>'S',19=>'T',20=>'U',21=>'V',22=>'W',23=>'X',24=>'Y',25=>'Z');
	}
	
	/**
	*
	* @excel导出
	* @作者			天发网络科技
	* @版权			http://www.tifaweb.com
	* @$array		数据数组
	* @-moder			所采用的模板 默认为template
	* @-title			标题
	* @-name			小标题（数组）
	* @--n					字段名
	* @--u					字段英文名
	* @--t					字段类型
	* @-content			数据(数组)
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function excelExport($array) {
		Vendor ( 'Excel.PHPExcel' );
		$letter=$this->letter();//引入列换算
		$mode=$array['moder']?$array['moder']:'t1.xls';	//获取模板
		$mode='Public/excel/'.$mode;
		//创建一个读Excel模版的对象
		$objReader = PHPExcel_IOFactory::createReader ( 'Excel5' );
		$objPHPExcel = $objReader->load ($mode);
		//获取当前活动的表
		$objActSheet = $objPHPExcel->getActiveSheet ();
		$objActSheet->setTitle ($array['title']);
		$baseRow = 2; //数据从N-1行开始往下输出  这里是避免头信息被覆盖
		//我现在就开始输出列头了
		foreach($array['name'] as $id=>$name){
			$objActSheet->setCellValue ($letter[$id].'1',$name['n']);
			foreach ( $array['content'] as $r => $dataRow ) {
				$row = $baseRow + $r;
				//将数据填充到相对应的位置
				$objPHPExcel->getActiveSheet ()->setCellValue ( $letter[$id] . $row,$dataRow [$name['u']]);
			}
		}
		//导出
		$filename = time ();
		
		header ( 'Content-Type: application/vnd.ms-excel' );
		header ( 'Content-Disposition: attachment;filename="' . $filename . '.xls"' ); //"'.$filename.'.xls"
		header ( 'Cache-Control: max-age=0' );
		
		$objWriter = PHPExcel_IOFactory::createWriter ( $objPHPExcel, 'Excel5' ); //在内存中准备一个excel2003文件
		$objWriter->save ( 'php://output' );
		return true;
	}
	
	/**
	 * @后台总数据统计
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function statistical(){
		$borrowing=M('borrowing');
		$userinfo=M('userinfo');
		$lines=M('lines');
		$recharge=M('recharge');
		$withdrawal=M('withdrawal');
		$forrecord=M('forrecord');
		$user=M('user');
		$money=M('money');
		$guaranteeapply=M('guaranteeapply');
		$array['autonym']=$userinfo->where('certification=1')->count();	//实名认证
		$array['video']=$userinfo->where('video_audit=1')->count();	//视频认证
		$array['scene']=$userinfo->where('site_audit=1')->count();	//现场认证
		$array['phone']=$userinfo->where('cellphone_audit=1')->count();	//手机认证
		$array['recharge']=$recharge->where('type=1 and genre=0')->count();	//充值申请
		$array['withdrawal']=$withdrawal->where('type=1')->count();	//提现申请
		//总
		$array['metotal']=$user->count();	//会员总数
		$array['mototals']=$money->sum('total_money');	//平台总资金
		$array['mototal']=number_format($array['mototals'],2,'.',',');
		$array['frtotal']=$money->sum('freeze_funds');	//冻结总资金
		$array['frtotal']=number_format($array['frtotal'],2,'.',',');
		$array['dutotal']=$money->sum('due_in');	//待收总资金
		$array['dutotal']=number_format($array['dutotal'],2,'.',',');
		$array['sttotal']=$money->sum('stay_still');	//待还总资金
		$array['sttotal']=number_format($array['sttotal'],2,'.',',');
		$array['ovtotal']=$money->sum('overdue');	//逾期总资金
		$array['ovtotal']=number_format($array['ovtotal'],2,'.',',');
		$array['wmototals']=$withdrawal->where('type=2')->sum('money');	//提现总资金
		$array['wmototal']=number_format($array['wmototals'],2,'.',',');
		$array['rmototals']=$recharge->where('type=2')->sum('money');	//充值总资金
		$array['rmototal']=number_format($array['rmototals'],2,'.',',');
		$array['gprofit']=number_format($array['rmototals']-$array['mototals']-$array['wmototals'],2,'.',',');	//平台总利润
		//今天
		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$where='time >='.$beginToday.' and time <='.$endToday;
		$wheres='audittime >='.$beginToday.' and audittime <='.$endToday;
		$array['menow']=$user->where($where)->count();	//会员数
		$array['wmonow']=$withdrawal->where('type=2 and '.$wheres)->sum('money');	//提现总资金
		$array['wmonow']=number_format($array['wmonow'],2,'.',',');
		$array['rmonow']=$recharge->where('type=2 and '.$wheres)->sum('money');	//充值总资金
		$array['rmonow']=number_format($array['rmonow'],2,'.',',');		
		//本周
		$time = time();
		//判断当天是星期几，0表星期天，1表星期一，6表星期六
		$w_day=date("w",$time);
 		//php处理当前星期时间点上，根据当天是否为星期一区别对待
	  	if($w_day=='1'){
			$cflag = '+0';
			$lflag = '-1';
	   	}
	  	else {
			  $cflag = '-1';
			  $lflag = '-2';
	   	}
		//本周一零点的时间戳
		$beginLastweek = strtotime(date('Y-m-d',strtotime("$cflag week Monday", $time)));        
		//本周末零点的时间戳
		$endLastweek = strtotime(date('Y-m-d',strtotime("$cflag week Monday", $time)))+7*24*3600;
		$where='time >='.$beginLastweek.' and time <='.$endLastweek;
		$wheres='audittime >='.$beginLastweek.' and audittime <='.$endLastweek;
		$array['meweeks']=$user->where($where)->count();	//会员数
		$array['wmoweeks']=$withdrawal->where('type=2 and '.$wheres)->sum('money');	//提现总资金
		$array['wmoweeks']=number_format($array['wmoweeks'],2,'.',',');
		$array['rmoweeks']=$recharge->where('type=2 and '.$wheres)->sum('money');	//充值总资金
		$array['rmoweeks']=number_format($array['rmoweeks'],2,'.',',');		
		//本月
		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y')); 
		$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
		$where='time >='.$beginThismonth.' and time <='.$endThismonth;
		$wheres='audittime >='.$beginThismonth.' and audittime <='.$endThismonth;
		$array['memonth']=$user->where($where)->count();	//会员数
		$array['wmomonth']=$withdrawal->where('type=2 and '.$wheres)->sum('money');	//提现总资金
		$array['wmomonth']=number_format($array['wmomonth'],2,'.',',');
		$array['rmomonth']=$recharge->where('type=2 and '.$wheres)->sum('money');	//充值总资金
		$array['rmomonth']=number_format($array['rmomonth'],2,'.',',');		
		return $array;
	}
	
	/**
	 * @查看头像是否存在
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function headPortrait($img){
		if(file_exists($img)){	//存在图片
			return 1;
		}
	}
	
	/**
	 * @获取某个类目下的文章
	 * @id			//栏目ID
	 * @limt		//显示条数
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function someArticle($id,$limt){
		$mod = D("Article");
		$list = $mod->field('id,title,addtime')->where("published=1 and catid=".$id)->limit($limt)->order('`order` desc,`addtime` desc')->select();
		return $list;
	}
	

	/**
	 * @版权管理
	 * @请不要做修改或删除，因多处调用此方法，如因自行修改造成的资金错误、软件不能正常使用后果自行承担
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function copyright($tf=0){
		if($tf){
			$systems=$this->systems();
			$curlPost = "dswjw=".$_SERVER['SERVER_NAME']."&dswjn=".DS_NUMbER."&dswji=".$_SERVER["REMOTE_ADDR"]."&dswje=".$systems['sys_email']."&dswjc=".$systems['sys_cellphone']."&dswjp=".$systems['sys_phone']."&dswja=".$systems['sys_address']."&dswjco=".$systems['sys_company'];
			$url='http://www.tifaweb.com/Api/Core/counter';  
			$in=$this->Curl($curlPost,$url);
			if($in['state']=='yes'){
				echo "已授权";
			}else{
				echo "未授权 授权免费，地址：http://www.tifaweb.com/Index/counter.html";
			}
		}
	}
	
	/**
	 * @短信发送
	 * @作者			shop猫
	 * @版权			宁波天发网络
	 * @官网			http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function textMessaging($number,$content){
		
	}
	
	/**
	*
	* @curl数据传输
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	* @curlPost	传输数据
	* @url		传输地址
	*/
	public function Curl($curlPost,$url){
		//$curlPost = "user=$username&pass=$password";
		//$url='http://xp.dswjjd.cn/index.php/Api/Index/login';  
		$ch = curl_init();  
		curl_setopt($ch, CURLOPT_POST, 1);  
		curl_setopt($ch, CURLOPT_URL,$url);  
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);  
		ob_start();  
		curl_exec($ch);  
		$json = ob_get_contents() ;  
		ob_end_clean();
		$login=json_decode($json,true);	
		return $login;
	}
	
	/**
	*
	* @数据库自动备份
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function automaticBackup(){
		$system=$this->systems();
		import('ORG.Custom.backupsql');
		$db = new DBManage ( C('DB_HOST'),C('DB_USER'), C('DB_PWD'), C('DB_NAME'), 'utf8' );
		$smtp=M('smtp');
		$stmpArr=$smtp->find();
		$backup=$db->backup();
		if($backup){
			$stmpArr['receipt_email']	=$system['sys_autoemail'];
			$stmpArr['title']			="数据库备份".time();
			$stmpArr['content']			='<div>
												备份时间:'.date('Y/m/d H:i:s').'
											</div>';
			$stmpArr['addattachment']	=$backup;
			$this->email_send($stmpArr);//发送邮件
			//删除备份的数据表
			if(file_exists($backup)){	
				unlink($backup);	//删除它
			}
		}
	}
	/**
	*
	* @显示指定目录文件
	* @dirname	要遍历的目录名字	
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function directory($dirname){
	   $num=0;    //用来记录目录下的文件个数
	   $dir_handle=opendir($dirname);
	   while($file=readdir($dir_handle))
	   {
		 if($file!="."&&$file!="..")
		 {
			$dirFile=$dirname."/".$file;
			$num++;
			$array[]=$file;
		 }
	   }
	   closedir($dir_handle);
	   $array['num']=$num;
	   return $array;
	}
	
	
	/**
	*
	* @模板数据获取
	* @dirname	要遍历的目录名字
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function templateData($dirname){
		$template=$this->directory($dirname);
		$array['num']=$template['num'];
		unset($template['num']);
		foreach($template as $id=>$te){
			$fp = file_get_contents($dirname."/".$te."/state.tf",'r'); 
			$array[$id] = explode("\r\n",$fp);
			$array[$id][3]=$te;
			fclose($fp); //关闭文件 
		}
		return $array;
	}
	
	/**
	*
	* @导出Word
	* @name		自定义名称(不支持中文)
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function exportWord($name){
		$dir_teaname = './Public/Word/';  //要创建的文件夹名称   Word
		//判断目录是否存在，存在就删除
		if(!is_dir($dir_teaname)){
		   //创建目录
			$mk = mkdir( $dir_teaname );
			if( !$mk )
			{
			 echo "创建目录失败！";
			 exit;
			}
		}
		//生成word文档
		import("ORG.Custom.Word"); 
		$savePath = $dir_teaname;
		$word = new word();	  
		$word->start();
		$this->display();
		$wordname = $name.'_'.time().'.doc'; //生成的word名称
		$wordname=iconv("utf-8","gb2312",$wordname);  //编码转换
		$word->save($savePath.$wordname);
		echo "<script>window.location.href='".__ROOT__."/Public/Word/".$wordname."';</script>";	
	}
	
	/**
	*
	* @删除指定文件
	* @path		路径
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function pathExit(){
		$path=$this->_post('img');
		if(file_exists($path)){	//存在图片
			unlink($path);	//删除它
		}
    }
	
	/**
	*
	* @担保公司
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	public function guaranteeComp(){
		$Gcomp=D('Guaranteecomp');
		$guaran=$Gcomp->field('id,name')->select();
		foreach($guaran as $g){
			$guara[$g['id']]=$g['name'];
		}
		return $guara;
	}
	
	/**
	*
	* @联动取值
	* @pid		类目
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function linkageValue($pid){
		$unite=M("unite");
		$industry=$unite->field('value,name')->where('pid='.$pid)->order('`id` ASC')->select();
		foreach($industry as $i){
			$ind[$i['value']]=$i['name'];
		}
		return $ind;
	}
	
	/**
	*
	* @防黑操作记录
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function webScan(){
		//用户唯一key
		define('WEBSCAN_U_KEY', '2133a3216620b018063b1c4392d28fde');
		//数据回调统计地址
		define('WEBSCAN_API_LOG', 'http://safe.webscan.360.cn/papi/log/?key='.WEBSCAN_U_KEY);
		//版本更新地址
		define('WEBSCAN_UPDATE_FILE','http://safe.webscan.360.cn/papi/update/?key='.WEBSCAN_U_KEY);
		//后台路径
		//define('WEBSCAN_DIRECTORY','Admin|admin');
		//url白名单,可以自定义添加url白名单,默认是对phpcms的后台url放行
		//写法：比如phpcms 后台操作url index.php?m=admin php168的文章提交链接post.php?job=postnew&step=post ,dedecms 空间设置edit_space_info.php
		//$webscan_white_url = array('index.php' => 'm=admin','post.php' => 'job=postnew&step=post','edit_space_info.php'=>'');
		//define('WEBSCAN_URL',$webscan_white_url);
		import("ORG.Custom.webscan"); 	
	}
	
	/**
	*
	* @邮件通知
	* @uid		用户ID
	* @uname	用户名
	* @title	标题
	* @content	内容
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function mailNotice($arr){
		$user=D('User');
		if($arr['uid']){
			$users=$user->where("id=".$arr['uid'])->find();
		}else{
			$users=$user->where('username="'.$this->_post('user').'"')->find();
		}
		$smtp=M('smtp');
		$stmpArr=$smtp->find();
		$stmpArr['receipt_email']	=$users['email'];
		$stmpArr['title']			=$arr['title'];
		$stmpArr['content']			=$arr['content'];
		
		$this->email_send($stmpArr);	
	}
	
	/**
	*
	* @站内信单发
	* @arr		数据
	*	fid		发送者ID	
	*   sid		收件者ID
	*	title	标题
	*  	msg		内容
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function  silSingle($arr){
		$Instation=M('instation');
		$arr['time']=time();
		return $Instation->add($arr);
	}
	
	/**
	*
	* @站内信回复
	* @arr		数据
	*	fid		发送者ID	
	*   sid		回复者ID
	*   pid		回复的站内信ID
	*  	msg		内容
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function  silReply($arr){
		$Instation=M('instation');
		$arr['time']=time();
		return $Instation->add($arr);
	}
	
	/**
	*
	* @站内信群发(限管理员)
	* @arr		数据	
	*   sid		收件用户组
	*	title	标题
	*  	msg		内容
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function  silMass($arr){
		$Instation=M('instation');
		$arr['sid']=array_filter(explode(",",$arr['sid']));
		$arr['sid']=json_encode($arr['sid']);
		$arr['time']=time();
		$arr['type']=1;
		return $Instation->add($arr);
	}
	
	/**
	*
	* @站内信发件箱
	* @uid		用户ID
	* @state	0未读1已读2删除
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function  silSend($uid,$state=''){
		$Instation=M('instation');
		if($state){
			$where=" and `state`=".$state;
		}
		return $Instation->where('`fid`='.$uid.$where)->select();
	}
	
	/**
	*
	* @站内信收件箱
	* @uid		用户ID
	* @state	0未读1已读2删除
	* @limit	条数
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function silReceipt($uid,$state='',$limit){
		$Instation=M('instation');
		if(isset($state)){
			$where=" and `state`=".$state;
		}else{
			$where=" and `state`<2";
		}
		if($limit){
			$instation=$Instation->where('`sid`='.$uid.$where)->order('`id` DESC')->limit($limit)->select();
		}else{
			$instation=$Instation->where('`sid`='.$uid.$where)->order('`id` DESC')->select();
		}
		
		//群发站内信
		$mass=$Instation->where('`type`=1'.$where)->order('`id` DESC')->select();
		foreach($mass as $id=>$m){
			$mass[$id]['sid']=json_decode($m['sid'], true);
			
			if(in_array($uid,$mass[$id]['sid'])){	//如果用户是收件人
				$instations[$id]=$m;
			}
		}
		
		unset($mass);
		if($instations && $instation){
			$instat=array_merge($instation,$instations);
			array_multisort($instat,SORT_DESC);
			return $instat;
		}else{
			return $instation;
			return $instations;
		}
	}
	
	/**
	*
	* @站内信收件箱
	* @id		站内信ID
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function singleReceipt($id){
		$Instation=M('instation');
		$find=$Instation->field('state,msg')->where('`id`="'.$id.'"')->find();
		if($find['state']<1){
			$Instation->where('`id`='.$id)->setField('state',1);
		}
		return $find['msg'];
	}
	
	/**
	*
	* @解决多次提交导致的误操作
	* @number	订单号
	* @type		1为AJAX
	* @content	提示的内容
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function bidPretreatment($number,$type=0,$content='您所操作的内容已发生改变，请重新操作！'){
		$borrow_log = M('borrow_log');
		$bolog=$borrow_log->where('`number`='.$number)->count();
		if($bolog>0){
			if($type==1){
				echo $content;
			}else{
				$this->error($content);
			}
			exit;
		}
	}
	
	/**
	*
	* @生成邮箱验证码
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function emailcode(){
		$emailcode=$_SESSION['emailcode']=substr(MD5(mt_rand()),6,6);	//生成验证码
		return $emailcode;
	}
	
	/**
	*
	* @直接跳转
	* @url		跳转地址
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function jumps($url){
		echo '<script>window.location.href="'.$url.'";</script>';
	}
	
	/**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
	 * @action $action 提交地址
     * @return 提交表单HTML文本
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
     */
	protected function requestForm($para_temp, $method, $button_name,$action) {
		//待请求参数数组
		$sHtml = "<form id='alipaysubmit' name='form1' action='".$action."' method='".$method."'>";
		while (list ($key, $val) = each ($para_temp)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
		//submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
		
		$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		
		return $sHtml;
	}
	
	/**
	 * @上传
	 * @approve	路径
     * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function upload($approve){
		import('ORG.Net.UploadFile');
		$upload = new UploadFile();// 实例化上传类
		$upload->maxSize  = 3145728 ;// 设置附件上传大小
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->savePath =  './Public/uploadify/uploads/'.$approve.'/';// 设置附件上传目录
		if(!$upload->upload()) {// 上传错误提示错误信息
		$this->error($upload->getErrorMsg());
		}else{// 上传成功 获取上传文件信息
		return $info =  $upload->getUploadFileInfo();
		}
	}
	
	/**
	 *
	 * @资金记录详细属性获取
	 * @id		值		
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 *
	 */
	protected function finetypeName($id){
		
		switch($id){
			case 1:
			$record='投资扣费';
			break;
			case 2:
			$record='收款';
			break;
			case 3:
			$record='充值';
			break;
			case 4:
			$record='提现';
			break;
			case 6:
			$record='奖励';
			break;
			case 7:
			$record='转账';
			break;
			case 8:
			$record='投资奖励';
			break;
			case 9:
			$record='融资';
			break;
			case 10:
			$record='扣费';
			break;
			case 11:
			$record='充值手续费';
			break;
			case 12:
			$record='提现撤销';
			break;
			case 13:
			$record='提现手续费';
			break;
			case 14:
			$record='借款管理费';
			break;
			case 15:
			$record='投资冻结资金';
			break;
			case 16:
			$record='投资撤回';
			break;
			case 17:
			$record='投资奖励扣除';
			break;
			case 19:
			$record='还款';
			break;
		}
		return $record;
	}

	
	/**
	*
	* @征信信息共享
	* @uid		用户ID
	* @作者		purl
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function creditShared($uid){
		//密钥验证,以下内容不可修改，修改后将无法使用征信功能
		if($uid>0){
			$userinfo=M('userinfo')->where('`uid`='.$uid)->find();
			$system=$this->systems();
			$userinfo['information']=array(
										'website'=>$system['sys_name'],
										'url'=>$_SERVER['SERVER_NAME']
										);
			unset($system);
			$userinfo=json_encode($userinfo);
			
			$json=$this->encryption($userinfo);	//加密
			$this->dsRealTransmission($json);
		}
	}
	
	/**
	 *	远程加解密
	 *	@json		需要参与加密的数组集
	 *  @type		0加密1解密
	 *  @作者		purl
	 *  @版权		宁波天发网络
	 *  @官网		http://www.tifaweb.com http://www.dswjcms.com
	 **/
	protected function encryption($json,$type=0){
		$md5json=MD5($json);
		$curlPost['md5json']=$md5json;	//加密后的信息
		$curlPost['json']=$json;	//原信息
		$curlPost['type']=$type;	//加密或解密
		
		$url=C('DS_CREDIT_URL').'encryption';  
		$in=$this->Curl($curlPost,$url);
		if($in['state']==88){
			return $in['value'];
		}
	} 
	
	/**
	 *	实名传输
	 *	@json	    需要参与加密的数组集
	 *  @作者		purl
	 *  @版权		宁波天发网络
	 *  @官网		http://www.tifaweb.com http://www.dswjcms.com
	 **/
	protected function dsRealTransmission($json){
		$md5json=MD5($json);
		$curlPost['md5json']=$md5json;	//加密后的信息
		$curlPost['json']=$json;	//原信息
		$url=C('DS_CREDIT_URL').'realTransmission'; 
		$in=$this->Curl($curlPost,$url);
		if($in['state']==1){
			echo $in['value'];
		}
	}
}
?>