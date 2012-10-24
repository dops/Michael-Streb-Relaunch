<?php /* Smarty version Smarty-3.1.7, created on 2012-01-13 09:35:52
         compiled from "/var/www/topdeals/htdocs/accounting/public/../application/accounting/view/template/form/form.tpl.html" */ ?>
<?php /*%%SmartyHeaderCode:7091476924f0fece82ee331-07317011%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '24e473075aac799857856d9899b3944a5ed158e2' => 
    array (
      0 => '/var/www/topdeals/htdocs/accounting/public/../application/accounting/view/template/form/form.tpl.html',
      1 => 1326385934,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '7091476924f0fece82ee331-07317011',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'name' => 0,
    'action' => 0,
    'method' => 0,
    'options' => 0,
    'elements' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f0fece82fdb6',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f0fece82fdb6')) {function content_4f0fece82fdb6($_smarty_tpl) {?><form name="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
" action="<?php echo $_smarty_tpl->tpl_vars['action']->value;?>
" method="<?php echo $_smarty_tpl->tpl_vars['method']->value;?>
" <?php echo $_smarty_tpl->tpl_vars['options']->value;?>
>
    <?php echo $_smarty_tpl->tpl_vars['elements']->value;?>

</form>
<div class="clear"></div><?php }} ?>