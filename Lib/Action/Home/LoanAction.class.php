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
class LoanAction extends HomeAction {
//-------------投资页--------------
	public function index(){
		//标题、关键字、描述
		$Site = D("Site");
		$site=$Site->field('keyword,remark,title,link')->where('link="'.$_SERVER['REQUEST_URI'].'"')->find();
		$this->assign('si',$site);
		$active['loan']='active';
		$this->assign('active',$active);
		$dirname=F('dirname');
		$endjs.='
//AJAX分页
$(function(){ 
	$(".pagination-centered a").click(function(){ 
		var loading=\'<div class="invest_loading"><div><img src="./Public/bootstrap/img/ajax-loaders/ajax-loader-1.gif"/></div><div>加载中...</div> </div>\';
		$(".loan_ajax").html(loading);
		$.get($(this).attr("href"),function(data){ 
			$("body").html(data); 
		}) 
		return false; 
	}) 
}) 
	
//条件选择数据保存
function integral(type,value){
	var types=$("#type").val();	//借款类型
	var states=$("#state").val();	//借款状态
	var loading=\'<div class="invest_loading"><div><img src="./Public/bootstrap/img/ajax-loaders/ajax-loader-1.gif"/></div><div>加载中...</div> </div>\';
	';
		$endjs.='$(".loan_ajax").html(loading);
		if(type=="type"){
			$("#type").val(value);	
			$("#types li").removeClass("active");
			$(".loan_ajax").load("__URL__/loanAjax", {type:value,state:states});
		}
		if(type=="state"){
			$("#state").val(value);	
			$("#states li").removeClass("active");
			$(".loan_ajax").load("__URL__/loanAjax", {type:types,state:value});
		}
		
	}
			';
		$this->assign('endjs',$endjs);
		$head="<script src='__PUBLIC__/js/timecount.js'></script>";
		$this->assign('head',$head);
		//名词解释
		$explanation=$this->someArticle(28,5);
		$this->assign('explanation',$explanation);
		//平台公告
		$new=$this->someArticle(32,5);
		$this->assign('new',$new);
		//帮助中心
		$help=$this->someArticle(31,5);
		$this->assign('help',$help);
		$this->display();
    }
	
	//标AJAX显示
	public function loanAjax(){
		$Borrowing = D('Borrowing');
		import('ORG.Util.Page');// 导入分页类
		$count      = $Borrowing->where($where)->count();// 查询满足要求的总记录数
		$Page       = new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
		$show       = $Page->show();// 分页显示输出
		$borrow=$this->borrow_unicoms($where,$Page->firstRow.','.$Page->listRows,'`stick` DESC,`time` DESC');
		if(!$borrow){
			echo '<div class="invest_loading"><div>暂无数据</div> </div>';
			exit;
		}
		
			foreach($borrow as $id=>$lt){
				//普通标
					$content.='
					<!-- 普通标 state--> 
					<div class="project-summary wall" style="position: relative;">
					';
					if($lt['state']==0){
					}else{
						$content.='<div class="bid-completed-stamp"></div>';
					}
					$content.='
						<div class="row-fluid">
							<div class="span8 ">
								<div style="min-height: 75px;">
									<h4  class="index_h4">
										<a href='.__ROOT__.'"/Loan/invest/'.$lt['id'].'.html" data-rel="tooltip" title="'.$lt['title'].'">'.$lt['title'].'</a>
									</h4>
									<p class="project-tags">
										<span class="label label-success">
					';
					if($lt['state']==0){
						$content.='投标中</span>';
					}else{
						$content.=$lt['state_name'].'</span>';
					}
					if($lt['stick']==1){
						$content.='<span class="tag" data-rel="tooltip" title="推荐"><i class="icon icon-darkgray icon-lightbulb"></i>推荐</span>';
					}
					if($lt['code']==1){
						$content.='<span class="tag" data-rel="tooltip" title="需要密码"><i class="icon icon-darkgray icon-locked"></i>密码</span>';
					}
					$content.='<span class="label label-warning" title="企业信用">'.$lt['privacy'].'</span>';
					$content.='
					</p>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<div class="pull-left" style="margin-right: 10px;">
					';
					if($lt['state']==0){
						$content.='<a class="btn btn-large btn-primary btn-details" href="'.__ROOT__.'/Loan/invest/'.$lt['id'].'.html">我要投资</a>';
					}else{
						$content.='<a class="btn btn-large btn-details" >'.$lt['state_name'].'</a>';
					}
					$content.='
					</div>
                        <div class="pull-left">
                            <div class="project-progress">	
					';
					
						$content.='
						<div class="progress progress-striped">
                                        <div class="bar" style="width: '.$lt['ratio'].'%;"></div>
                                    </div>
						';
					
					$content.='</div>';
					
					$content.='
					</div>
									</div>
								</div>
							</div>
							<div class="span4">
								<ul class="project-summary-items">
									<li><span class="title">融资金额</span>'.number_format($lt['money'],2,'.',',').' 元</li>
									<li><span class="title">年化收益</span> 
										<span class="important data-tips">
										'.$lt['rates'].'%
										</span>
									</li>
									<li><span class="title">融资期限</span>
										<span class="data-tips">
											'.$lt['deadlines'].'
										</span>
									</li>
									
								</ul>
							</div>
						</div>
					</div>
					<script>timeCount("limittime'.$lt['id'].'");</script> 
					<!-- 普通标 end-->
					';
			}
			$content.='
			<div class="pagination pagination-centered">
			<ul>'.$show.'</ul>
			</div>
			<script>
			//AJAX分页
			$(function(){ 
				$(".pagination-centered a").click(function(){ 
					var loading=\'<div class="invest_loading"><div><img src="../Public/bootstrap/img/ajax-loaders/ajax-loader-1.gif"/></div><div>加载中...</div> </div>\';
					$(".loan_ajax").html(loading);
					$.get($(this).attr("href"),function(data){ 
						$(".loan_ajax").html(data); 
					}) 
					return false; 
				}) 
			}) 		
			</script>';
		
		echo $content;
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
		//投标记录
		$bid_records=$this->bidRecords('2',$id);
		
		foreach($bid_records as $id=>$b){
			$bid_records[$id]['uname']=mb_substr($b['actionname']['uname'],0,1)."***".mb_substr($b['actionname']['uname'],-1);
		}
		$Guara['bid_records']=$bid_records;
		//进度
		$Guara['ratio']=sprintf("%01.2f",($Guara['borrowing']['money']-$Guara['borrowing']['surplus'])/$Guara['borrowing']['money']*100);
		//已投金额
		$Guara['already']=($Guara['borrowing']['money']-$Guara['borrowing']['surplus'])?($Guara['borrowing']['money']-$Guara['borrowing']['surplus']):'0.00';
		$data=explode(";",$Guara['borrowing']['data']);
		$Guara['pact']=explode(",",$data[0]);
		$Guara['indeed']=explode(",",$data[1]);
		$this->assign('bo',$Guara);
		$money=M('money');
		$money=$money->field('total_money,available_funds,freeze_funds')->where('`uid`='.$this->_session('user_uid'))->select();
		$money=reset($money);
		$this->assign('money',$money);
		
		$endjs.='
			function changeVerify(){
				var timenow = new Date().getTime();
				document.getElementById("verifyImg").src="'.__APP__.'/Public/verify/"+timenow;
			}';
		
			$endjs.='
			/*
			 * @投标金额事件
			 * @uid			1减2加3最大金额4键入时
			 * @gpfd		借款还需金额
			 * @yu			余额
			 * @surplus		可投金额
			 * @maxs		最大金额
			 * @mins		最小金额
			 */
			 function Totalprice(uid,gpfd,yu,maxs,mins){		
				var ordvalue=$("#price").val();						//获取输入框的值
				var strP=/^\d+$/;										//数字正则
				var surplus="";
				var smalls="";
				if(maxs>0){
					if(yu>gpfd && yu>maxs){
						if(gpfd>maxs){
							surplus=maxs;
						}else{
							surplus=gpfd;
						}
					}else if(yu<=gpfd && yu<=maxs){
						surplus=yu;
					}else if(yu<=gpfd && yu>=maxs){
						surplus=maxs;
					}else{
						surplus=gpfd;
					}
				}else{
					if(yu>gpfd){
						surplus=gpfd;
					}else{
						surplus=yu;
					}
				}
				if(mins>1 && mins>gpfd){
					smalls=gpfd;
				}else if(mins>1 && mins<=gpfd){
					smalls=mins;
				}else{
					smalls=1;
				}
				if(uid==1){		//减
					var cut=parseInt(ordvalue)-1;							//减1
						if(parseInt(ordvalue) <= smalls){
								$("#price").val(smalls);
						}else{
								$("#price").val(cut);
						}
				}else if(uid==2){	//加
					var add=parseInt(ordvalue)+1;								//加1
						if(parseInt(ordvalue) >= (surplus)){
								if(jcpkc==smalls){
									$("#price").val(smalls);
								}else{
								$("#price").val(surplus);
								}
						}else{
							$("#price").val(add);
						}
				}else if(uid==3){	//最大金额
					$("#price").val(Math.floor(surplus));
				}else if(uid==4){	//键入时
					if(strP.test(ordvalue)){		//如果是数字
						if(parseInt(ordvalue) <= smalls){
							$("#price").val(smalls);
							var ordvalue=smalls;
						}else if(parseInt(ordvalue) >= surplus){
							$("#price").val(surplus);
						}
					}else{		//如果不是数字
						$("#price").val(smalls);
					}
				}
			 }
		';
		$this->assign('endjs',$endjs);
		 //标题、关键字、描述
		$integral['link']=1;
		
		$this->assign('si',$integral);
		$integral['title']=$Guara['borrowing']['title'];
		$this->assign('si',$integral);
		$active['loan']='active';
		$this->assign('active',$active);
		$this->display();
	}
}