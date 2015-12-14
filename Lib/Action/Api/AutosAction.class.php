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
class AutosAction extends CommAction {
	
	public function index(){	
		$this->automaticBackup();	//数据库备份邮箱改送
	}
	public function timing(){
		//还款提前提醒
		$refund=D('Refund');
		$system=$this->systems();
		$time=strtotime("+$system[sys_refundDue] day");	//提前提醒设置的时间
		$refun=$refund->relation(true)->where('uid='.$this->_session('user_uid').' and type=0 and time<='.$time)->select();
		foreach($refun as $r){
		//邮件通知
		$mailNotice['uid']=$r['uid'];
		$mailNotice['title']='还款提前提醒';
		$mailNotice['content']='
			<div style="margin: 6px 0 60px 0;">
				<table class="table table-bordered table-hover">
                    <thead>
                      <tr>
					  	<th>标题</th
                        <th>还款时间</th>
                        <th>还款金额</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
					  	<td>'.$r['title'].'</td>
                        <td>'.date('Y-m-d H:i:s',$r['time']).'</td>
                        <td>'.number_format($r['money'],2,'.',',').' 元</td>
                      </tr>
                    </tbody>
                </table>p>
			</div>
			<div style="color: #999;">
				<p>发件时间：'.date('Y/m/d H:i:s').'</p>
				<p>此邮件为系统自动发出的，请勿直接回复。</p>
			</div>';
		$this->mailNotice($mailNotice);
		}
	}
}