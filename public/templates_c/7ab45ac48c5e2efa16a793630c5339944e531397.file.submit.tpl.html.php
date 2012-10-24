<?php /* Smarty version Smarty-3.1.7, created on 2012-01-13 09:33:39
         compiled from "/var/www/topdeals/htdocs/accounting/public/../application/accounting/view/template//form/submit.tpl.html" */ ?>
<?php /*%%SmartyHeaderCode:4449013674f0fec63085169-29750550%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '7ab45ac48c5e2efa16a793630c5339944e531397' => 
    array (
      0 => '/var/www/topdeals/htdocs/accounting/public/../application/accounting/view/template//form/submit.tpl.html',
      1 => 1326385934,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '4449013674f0fec63085169-29750550',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'prefix' => 0,
    'wrapperId' => 0,
    'classes' => 0,
    'name' => 0,
    'elementId' => 0,
    'value' => 0,
    'options' => 0,
    'suffix' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f0fec630a6a4',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f0fec630a6a4')) {function content_4f0fec630a6a4($_smarty_tpl) {?><?php if (isset($_smarty_tpl->tpl_vars['prefix']->value)){?><?php echo $_smarty_tpl->tpl_vars['prefix']->value;?>
<?php }?>
<div id="<?php echo $_smarty_tpl->tpl_vars['wrapperId']->value;?>
" class="field fieldSubmit <?php echo $_smarty_tpl->tpl_vars['classes']->value;?>
">
    <div class="elementWrapper">
        <input type="submit" name="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
" id="<?php echo $_smarty_tpl->tpl_vars['elementId']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['value']->value;?>
" <?php echo $_smarty_tpl->tpl_vars['options']->value;?>
 />
    </div>
</div>
<?php if (isset($_smarty_tpl->tpl_vars['suffix']->value)){?><?php echo $_smarty_tpl->tpl_vars['suffix']->value;?>
<?php }?>
<div class="clear"></div><?php }} ?>