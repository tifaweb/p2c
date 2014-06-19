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
class IndexAction extends HomeAction {
    public function index(){
		$this->copyright();
		$where='';
		$borrow=$this->borrow_unicoms($where,'5','`stick` DESC,`time` DESC');
		$this->assign('borrow',$borrow);
		//新闻中心
		$new=$this->someArticle(16,5);
		$this->assign('new',$new);
		//累计投资金额
		$borrowing = M('borrowing');
		$accumulate['sum']=$borrowing->where('`state`>1')->sum('money');
		//累计预期收益
		$money = M('money');
		$accumulate['benefit']=$money->sum('`stay_interest`+`make_interest`+`make_reward`');
		$this->assign('accumulate',$accumulate);
		//投资排行
		$borrow_log = M('borrow_log');
		$User = M('user');
		$user=$User->field('id,username')->select();
		foreach($user as $u){
			$users[$u['id']]=$u['username'];
		}
		//本周
		$w_day=date("w",time());
	  	if($w_day=='1'){
			$cflag = '+0';
			$lflag = '-1';
	   	}
	  	else {
			  $cflag = '-1';
			  $lflag = '-2';
	   	}
		$beginLastweek = strtotime(date('Y-m-d',strtotime("$cflag week Monday",time())));        
		$endLastweek = strtotime(date('Y-m-d',strtotime("$cflag week Monday", time())))+7*24*3600;
		//本月
		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y')); 
		$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
		$bidRecords=$borrow_log->where('`type`=7 or `type`=15')->select();
		foreach($bidRecords as $id=>$b){
			$bid=json_decode($b['actionname'], true);
			$name=$users[$bid['uid']];
			if(($b['time']>=$beginLastweek) and ($b['time']<=$endLastweek)){	//本周
				if(array_key_exists($bid['uid'],$transit)){
					$tday[$bid['uid']]['operation']=$tday[$bid['uid']]['operation']+$bid['operation'];	
				}else{
					$tday[$bid['uid']]['operation']=$bid['operation'];
				}
				$tday[$bid['uid']]['username']=mb_substr($name,0,1)."***".mb_substr($name,-1);
			}
			if(($b['time']>=$beginThismonth) and ($b['time']<=$endThismonth)){	//本月
				if(array_key_exists($bid['uid'],$transit)){
					$tmonth[$bid['uid']]['operation']=$tmonth[$bid['uid']]['operation']+$bid['operation'];	
				}else{
					$tmonth[$bid['uid']]['operation']=$bid['operation'];
				}
				$tmonth[$bid['uid']]['username']=mb_substr($name,0,1)."***".mb_substr($name,-1);
			}
			//总
			if(array_key_exists($bid['uid'],$transit)){
				$transit[$bid['uid']]['operation']=$transit[$bid['uid']]['operation']+$bid['operation'];	
			}else{
				$transit[$bid['uid']]['operation']=$bid['operation'];
			}
			$transit[$bid['uid']]['username']=mb_substr($name,0,1)."***".mb_substr($name,-1);
			unset($bid);
			unset($name);
		}
		arsort($tday);	//周排名
		arsort($tmonth); //月排名
		arsort($transit);	//总排名
		$this->assign('tday',array_slice($tday,0,5));
		$this->assign('tmonth',array_slice($tmonth,0,5));
		$this->assign('transit',array_slice($transit,0,5));
		
		$shuffling = M('shuffling');
		$shufflings=$shuffling->field('title,img')->order('`order` ASC')->select();
		$this->assign('shuff',$shufflings);
		$head="<link href='__PUBLIC__/css/jslides.css' rel='stylesheet'>";
		$this->assign('head',$head);
		//标题、关键字、描述
		$Site = D("Site");
		$site=$Site->field('keyword,remark,title,link')->where('link="'.$_SERVER['REQUEST_URI'].'"')->find();
		$this->assign('si',$site);
		$active['index']='active';
		$this->assign('active',$active);
		
		$endjs='
		//首页轮播
		$(function(){$("#kinMaxShow").kinMaxShow();});
		
		';
		$this->assign('endjs',$endjs);
		
		$this->display();
    }
//-------计算器
	public function counter(){
		//标题、关键字、描述
		$Site = D("Site");
		$site=$Site->field('keyword,remark,title,link')->where('link="'.$_SERVER['REQUEST_URI'].'"')->find();
		$this->assign('si',$site);
		$active['counter']='active';
		$this->assign('active',$active);
		$endjs='
			function switchover(id){
				if(id==1){
					$("#deadline1").hide();$("#deadline").show();$("#deadline").attr("name","deadline");$("#deadline1").attr("name","");
				}else{
					$("#deadline1").show();$("#deadline").hide();$("#deadline1").attr("name","deadline");$("#deadline").attr("name","");
				}
			}
			function counterOclick(){
				var money=$("[name= \'money\']").val();
				var rate=$("[name= \'rate\']").val();
				var units=$("input[name=\'unit\']:checked").val(); ;
				var deadline=$("[name= \'deadline\']").val();
				var way=$("[name= \'way\']").val();
				$("#sounter_result").html(\'<div class="invest_loading"><div><img src="__PUBLIC__/bootstrap/img/ajax-loaders/ajax-loader-1.gif"/></div><div>加载中...</div></div>\');
				$("#sounter_result").load("__URL__/counterAdd", {money:money,rate:rate,units:units,deadline:deadline,way:way});
			}
		';
		$this->assign('endjs',$endjs);
		$this->display();
    }
	
	//显示结果
    public function counterAdd(){	
		$counters=$this->counters($this->_post('money'),$this->_post('rate'),$this->_post('deadline'),$this->_post('units'),$this->_post('way'));
		if($this->_post('money')<1){
			echo '
				<div class="invest_loading">
					<div>没有数据</div>
				</div>
				';
				exit;
		}
		$total=$counters['total'];
		$interest=$counters['interest'];
		unset($counters['total']);
		unset($counters['interest']);
		foreach($counters as $id=>$i){
			$ajax.='
				 <tr>
                    <td>'.($id+1).'</td>
                    <td>'.$i['refund'].'</td>
                    <td>'.$i['capital'].'</td>
                    <td>'.$i['interest'].'</td>
                    <td>'.$i['remaining'].'</td>
                 </tr>
					';
		}
		echo " <div class='couter_total'><span>累计支付利息：<i>".number_format($interest,2,'.',',')."元</i></span><span>累计还款总额：<i>".number_format($total,2,'.',',')."元</i></span></div>";
		echo '
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="span2">期数</th>
						<th class="span3">还款总额</th>
						<th class="span2">还款本金</th>
						<th class="span2">还款利息</th>
						<th class="span3">还需还款本金</th>
					</tr>
				</thead>
				
				<tbody>	
			';
		echo $ajax;
		echo '
			</tbody>
        </table>
			';
    }
	
//-------所有担保公司
	public function companylist(){
		$guaranteecomp=M('guaranteecomp');
		$comp=$guaranteecomp->field('id,name,logo,keyword,remark')->select();
		$this->assign('comp',$comp);
		$this->display();
	}
	
	//担保公司
	public function company(){
		$id=$this->_get('id');
		if(!$id){
			$this->error('参数有误！');
		}
		$guaranteecomp=M('guaranteecomp');
		$comp=$guaranteecomp->field('name,logo,content,img')->where('id='.$id)->find();
		$comp['img']=explode(",",$comp['img']);
		$this->assign('comp',$comp);
		$this->display();
	}
}