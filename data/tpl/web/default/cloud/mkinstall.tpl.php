<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>


<div class="alert alert-info">
	1、云端在线安装请先授权您的站点，如果没有授权，请联系客服授权。<br>
	2、下载完成后，请到公众号应用里的【未安装应用】里面首次安装。<br>
</div>
<table class="table we7-table table-hover">
	<col width="120px"/>
	<col width="140px"/>
	<col width="100px"/>
	<col width="100px"/>
	<col width="50px"/>
	<col/>
	<tr>
		<th class="text-left">模块名称</th>
		<th>模块标识</th>
		<th>版本号</th>
		<th class="text-right bg-light-gray">操作</th>
		<th class="text-right bg-light-gray">类别</th>
	</tr>
	<?php  if($result && $total) { ?>
		<?php  if(is_array($result)) { foreach($result as $row) { ?>
	<tr>
		<td class="text-left"><?php  echo $row['title'];?></td>
		<td><?php  echo $row['name'];?></td>
        <?php  if(!module_ver($row['name']) ) { ?>
          <td><?php  echo $row['version'];?></td>
          <?php  } else { ?>
          <?php  if($row['version'] > module_ver($row['name']) ) { ?>
          <td style="color:red">升级<?php  echo module_ver($row['name'])?>-><?php  echo $row['version'];?></td>
          <?php  } else { ?>
          <td><?php  echo $row['version'];?></td>
          <?php  } ?>
        <?php  } ?>
		<td class="text-left">
			<div class="link-group">
              <?php  if($row['found'] == 0 ) { ?>
				<a href="<?php  echo url('cloud/moduleup');?>m=<?php  echo $row['name'];?>&d=prepare">下载</a>
              <?php  } else { ?>
              	<?php  if($row['version'] > module_ver($row['name']) ) { ?>
              	    <?php  if($row['update_url']=='#' ) { ?>
					<a href="<?php  echo url('cloud/moduleup');?>m=<?php  echo $row['name'];?>&d=prepare">更新模块</a>
					<?php  } else { ?>
					<a href="#">&raquo;&raquo;&raquo;&raquo;&raquo;&raquo;&raquo;</a>
					<?php  } ?>
              	<?php  } else { ?>
              		<a href="<?php  echo url('cloud/moduleup');?>m=<?php  echo $row['id'];?>"></a>
              	<?php  } ?>
              <?php  } ?>
                <?php  if($row['update_url']=='#' ) { ?>
              	<a href="<?php  echo url('cloud/moduleup');?>m=<?php  echo $row['name'];?>&d=prepare" data-toggle="modal" >检测更新</a>
              	<?php  } else { ?>
              	<a href="<?php  echo $row['update_url'];?>" style="color:green">独立更新</a>
              	<?php  } ?>
			</div>
		</td>
		<td class="text-left">
			<div class="link-group">
				<?php  if($row['edition']=='0' ) { ?>
              <a href="#" data-toggle="modal" >免费版</a>
              <?php  } else { ?>
              <a href="##<?php  echo $row['name'];?>" style="color:red" data-toggle="modal" >授权版</a>
              <?php  } ?>
			</div>
		</td>
	</tr>
	<div class="modal fade" id="<?php  echo $row['name'];?>" role="dialog">
		<div class="we7-modal-dialog modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">
						<span aria-hidden="true">&times;</span>
					</button>
					<div class="modal-title">自助授权</div>
				</div>
				<div class="modal-body">
					<div class="text-center">
						<iframe src="<?php  echo $auth_url;?><?php  echo base64_encode('{"modnamemm":"'.$row['title'].'","modname":"'.$row['name'].'","url":"'.$hosturl.'","forward":"profile"}') ?>" marginheight="0" marginwidth="0" frameborder="0" width="580px" style="height:530px; margin: 0 -30px;" scrolling="no" allowTransparency="true"></iframe>					</div>
				</div>
			</div>
			</form>
		</div>
	</div>
		<?php  } } ?>
	<?php  } ?>
</table>

							<!----------------------------------------->
<div class="pull-right">
	<?php  echo $pager;?>
</div>
	<div class="modal fade" id="invite" role="dialog">
		<div class="we7-modal-dialog modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">
						<span aria-hidden="true">&times;</span>
						<span class="sr-only">Token</span>
					</button>
					<div class="modal-title">系统提示</div>
				</div>
				<div class="modal-body">
					<div class="text-center">
						该模块暂无独立更新！					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary js-invite-code" data-dismiss="modal">确定</button>
				</div>
			</div>
			</form>
		</div>
	</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>