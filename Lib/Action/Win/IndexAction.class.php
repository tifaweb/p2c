<?php
// +----------------------------------------------------------------------
// | dswjcms
// +----------------------------------------------------------------------
// | Copyright (c) http://www.tifaweb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
// +----------------------------------------------------------------------
// | Author: 宁波市鄞州区天发网络科技有限公司 <dianshiweijin@126.com>
// +----------------------------------------------------------------------
// | Released under the GNU General Public License
// +----------------------------------------------------------------------
// | author: purl
// +----------------------------------------------------------------------
// | time: 2016-12-15
// +----------------------------------------------------------------------
defined('THINK_PATH') or exit();
class IndexAction extends WinAction {
    public function index(){
		$shuffling = M('shuffling');
		$shufflings=$shuffling->field('title,img,url')->where('`state`=0 and type=1')->order('`order` ASC')->select();
		$this->assign('shufflings',$shufflings);
		//借款列表
		import('@.Plugin.DswjcmsApp.Pages');
		$where='(`state` !=3) and code=0';
		$count      = M('borrowing')->where($where)->count();
		$Page       = new Pages($count,5);
		$Page->setConfig('theme',"%first% <li class='now'>%nowPage%/%totalPage%</li> %linkPage%");
		$show       = $Page->show();
		$borrow=M('borrowing')->field('id,time,type,title,money,rates,deadline,way,state,surplus,limittime')->where($where)->order('`stick` DESC,`time` DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		foreach($borrow as $id=>$bo){
			$borrow[$id]['ratio']=sprintf("%01.2f",($bo['money']-$bo['surplus'])/$bo['money']*100);
			if($bo['money']>=10000){
				$borrow[$id]['money']=number_format($bo['money']/10000,0,'.',',').'万';
			}else{
				$borrow[$id]['money']=number_format($bo['money'],2,'.',',');
			}
			if($bo['candra']==1){
				$borrow[$id]['deadline']=$bo['deadline'].'天';
			}else{
				$borrow[$id]['deadline']=$bo['deadline'].'个月';
			}
		}
		$this->assign('borrow',$borrow);
		$this->assign('show',$show);
		$this->display();
    }
	
//-------------投资详细页--------------
	public function invest(){
		$coverdue=M('coverdue');
		$id=(int)$this->_get('id');	
		$Guarantee = D("Guarantee");
		$Guara=$Guarantee->where('`bid`='.$id)->relation(true)->find();
		//企业信用
		$privacy=$this->linkageValue(1);
		$Guara['borrowing']['privacys']=$privacy[$Guara['borrowing']['privacy']];
		//还款方式
		$way=$this->linkageValue(4);
		$Guara['way']=$way[$Guara['borrowing']['way']];
		//已投金额
		$Guara['already']=($Guara['borrowing']['money']-$Guara['borrowing']['surplus'])?($Guara['borrowing']['money']-$Guara['borrowing']['surplus']):'0.00';
		$data=explode(";",$Guara['borrowing']['data']);
		$Guara['pact']=explode(",",$data[0]);
		$Guara['indeed']=explode(",",$data[1]);
		$this->assign('borrow',$Guara);
		$this->display();
	}
	
	//投标确认
	public function cast(){
		$this->homeVerify();
		$id=$this->_get('id');
		if($id<1){
			echo "非法操作，你的操作已被记录，网警正在锁定";
			 exit;
		}
		
		$borrow=M('borrowing')->where('`id`="'.$this->_get('id').'"')->find();
		$borrow['available']=M('money')->where('`uid`="'.$this->_session('user_uid').'"')->getField('available_funds');
		$this->assign('borrow',$borrow);
		$this->display();
	}
	
	//投标记录
	public function record(){
		$id=$this->_get('id');
		if($id<1){
			echo "非法操作，你的操作已被记录，网警正在锁定";
			 exit;
		}

		import('@.Plugin.DswjcmsApp.Pages');
		$where='(`bid`='.$id.' and `type` =4)';
		$count      = M('borrow_log')->where($where)->count();
		$Page       = new Pages($count,10);
		$Page->setConfig('theme',"%first% <li class='now'>%nowPage%/%totalPage%</li> %linkPage%");
		$show       = $Page->show();
		$log=M('borrow_log')->field('uid,type,time,actionname')->where($where)->order('`time` DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($log as $i=>$l){
			$actionname=json_decode($l['actionname'], true);
			$log[$i]['operation']=$actionname['operation'];
			$log[$i]['uname']=mb_substr($actionname['uname'],0,1,'utf-8')."***".mb_substr($actionname['uname'],-1,1,'utf-8');
			unset($actionname);
		}
		$this->assign('log',$log);
		$this->assign('show',$show);
		$this->display();
	}
}