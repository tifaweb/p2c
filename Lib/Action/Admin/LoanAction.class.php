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
class LoanAction extends AdminCommAction {
    public function index(){
		
		$Borrowing = D("Borrowing");
		$list=$Borrowing->field('id,title,rates,limittime,deadline,money,state')->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	//发布/查看
	public function page(){
		$id=$this->_get('id');
		//联动
		$linkage=$this->borrowLinkage();
		$this->assign('linkage',$linkage);
		//担保公司
		$Gcomp=D('Guaranteecomp');
		$guaran=$Gcomp->field('id,name')->select();
		$this->assign('guaran',$guaran);
		foreach($guaran as $g){
			$guara[$g['id']]=$g['name'];
		}
		if($this->_get("pid")==1){//分配

		}else{	//修改
			$Guara['gcompanys']=$guara[$Guara['gcompany']];
		}
		$this->assign('Guara',$Guara);
		if($id>0){	//查看	
			//担保公司
			$guara=$this->guaranteeComp();
			//机构申请
			$Guarantee = D("Guarantee");
			$Guara=$Guarantee->where('`bid`="'.$id.'"')->relation('borrowing')->find();
			$data=explode(";",$Guara['borrowing']['data']);//分割合同协议和企业实地照片
			$Guara['img']=explode(",",$data[0]);
			$Guara['agr']=explode(",",$data[1]);
			$this->assign('Guara',$Guara);
			
			$this->assign('guara',$guara[$Guara['gid']]);
		}
		$this->display();
	}
	
	//提交
	public function institutionIssue(){
		//借款表
		$Borrowing=D('Borrowing');
		if(!$creates=$Borrowing->create()){
				$this->error($Borrowing->getError());
		}
		//机构担保特有表
		$Guarantee=D('Guarantee');
		
		if(!$create=$Guarantee->create()){
			$this->error($Guarantee->getError());
		}
		$creates['endtime']=time()+86400*$creates['valid'];	//失效时间
		$month=$creates['deadline'];
		$creates['limittime']=strtotime("+$month month");	//结束时间
		$img=implode(",",$this->_post('img'));
		$agr=implode(",",$this->_post('agr'));
		$creates['data']=$img.';'.$agr;
		$creates['time']=time();
		
		$borrow=$Borrowing->add($creates);
		
		if($borrow){
			$create['bid']=$borrow;//标ID
			$this->irefunds($borrow);//收款记录
			$last=$Guarantee->add($create);
		}else{
			$this->error('添加出错，请联系管理员');
		}
		
		if($last){
			$this->Record('发布标成功');//后台操作
				$this->success('发布成功');
		}else{
			$this->Record('发布标失败');//后台操作
				$this->error('发布失败');
		}
		
	}
	
	//修改
	public function amend(){
		//借款表
		$Borrowing=D('Borrowing');
		if(!$creates=$Borrowing->create()){
				$this->error($Borrowing->getError());
		}
		//机构担保特有表
		$Guarantee=D('Guarantee');
		
		if(!$create=$Guarantee->create()){
			$this->error($Guarantee->getError());
		}
		$img=implode(",",$this->_post('img'));
		$agr=implode(",",$this->_post('agr'));
		$creates['data']=$img.';'.$agr;
		$borrow=$Borrowing->where('`id`="'.$this->_post('id').'"')->save($creates);
		$last=$Guarantee->where('`bid`="'.$this->_post('id').'"')->save($create);
	  $this->Record('更新标成功');//后台操作
		$this->success('更新成功');
	}
	
	//还款计划
	public function plan(){
		$refund=M('refund');
		$id=$this->_get('id');
		$refun=$refund->where('bid="'.$id.'"')->order('time ASC')->select();
		$this->assign('refun',$refun);
		$this->display();
	}
	
	//投资记录
	public function records(){
		$record=$this->bRecord();
		$this->assign('record',$record);
		$this->display();
	}
	
	//导出EXCEL(投资记录)
	public function recordExport(){
		$list=$this->bRecord();
		$data['title']="投资记录";
		$data['name']=array(
							array('n'=>'ID','u'=>'id'),
							array('n'=>'用户ID','u'=>'uid'),
							array('n'=>'用户组','u'=>'type'),
							array('n'=>'总金额','u'=>'total'),
							array('n'=>'可用金额','u'=>'available'),
							array('n'=>'操作金额','u'=>'operation'),
							array('n'=>'操作说明','u'=>'instructions'),
							);
		foreach($list as $l){
			switch($l['type']){
				case 1:
				$type="投资人";
				break;
				case 2:
				$type="借款人";
				break;
				case 3:
				$type="投资人";
				break;
				case 4:
				$type="借款人";
				break;
			}
			$content[]=array(
							'id'				=>' '.$l['id'],
							'uid'				=>' '.$l['actionname']['uid'],
							'type'				=>$type,
							'total'				=>$l['actionname']['total'],
							'available'			=>$l['actionname']['available'],
							'operation'			=>$l['actionname']['operation'],
							'instructions'		=>strip_tags($l['actionname']['instructions'])
							);
		}
		$data['content']=$content;
		$excel=$this->excelExport($data);
		$this->Record('投资记录导出成功');//后台操作
			$this->success("导出成功","__APP__/TIFAWEB_DSWJCMS/Loan/record.html");
		
	}
}
?>