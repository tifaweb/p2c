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
class GuaranteeModel extends RelationModel{
	protected $_validate = array(
		array('proving','require','验证码必须！'), 
		array('proving','checkCode','验证码错误!',0,'callback',1),
		array('opinion','require','担保机构意见有误！'),
		array('use','require','资金用途有误！'),
		array('source','require','还款来源有误！'),
		array('information','require','抵押物信息有误！'),
		array('measures','require','风险控制措施有误！'),
		array('synopsis','require','企业简介有误！'),
		array('scope','require','营业范围有误！'),
		array('business','require','经营状况有误！'),
	);
	
	protected function checkCode($code){
		if(md5($code)!=session('verify')){
			return false;
		}else{
			return true;
		}
	}
	protected $_link=array(
		'borrowing'=> array(  
			'mapping_type'=>BELONGS_TO,
			'class_name'=>'borrowing',
            'foreign_key'=>'bid',
            'mapping_name'=>'borrowing',
		),
		'guaranteecomp'=> array(  
			'mapping_type'=>BELONGS_TO,
			'class_name'=>'guaranteecomp',
            'foreign_key'=>'gid',
            'mapping_name'=>'guaranteecomp',
			'mapping_fields'=>'name',
			'as_fields'=>'name:gname',
		),
	);
}
?>