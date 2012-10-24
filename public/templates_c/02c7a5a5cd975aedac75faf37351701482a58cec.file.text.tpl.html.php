<?php /* Smarty version Smarty-3.1.7, created on 2012-01-13 09:33:38
         compiled from "/var/www/topdeals/htdocs/accounting/public/../application/accounting/view/template//form/text.tpl.html" */ ?>
<?php /*%%SmartyHeaderCode:15857446584f0fec62f21fb9-42420279%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '02c7a5a5cd975aedac75faf37351701482a58cec' => 
    array (
      0 => '/var/www/topdeals/htdocs/accounting/public/../application/accounting/view/template//form/text.tpl.html',
      1 => 1326385934,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '15857446584f0fec62f21fb9-42420279',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'prefix' => 0,
    'wrapperId' => 0,
    'classes' => 0,
    'label' => 0,
    'elementId' => 0,
    'name' => 0,
    'value' => 0,
    'options' => 0,
    'description' => 0,
    'suffix' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f0fec630436f',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f0fec630436f')) {function content_4f0fec630436f($_smarty_tpl) {?><?php if (isset($_smarty_tpl->tpl_vars['prefix']->value)){?><?php echo $_smarty_tpl->tpl_vars['prefix']->value;?>
<?php }?>
<div id="<?php echo $_smarty_tpl->tpl_vars['wrapperId']->value;?>
" class="field fieldText <?php echo $_smarty_tpl->tpl_vars['classes']->value;?>
">
    <div class="labelWrapper">
        <?php if ($_smarty_tpl->tpl_vars['label']->value){?><label for="<?php echo $_smarty_tpl->tpl_vars['elementId']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['label']->value;?>
</label><?php }?>
    </div>
    <div class="elementWrapper">
        <input type="text" name="<?php echo $_smarty_tpl->tpl_vars['name']->value;?>
" id="<?php echo $_smarty_tpl->tpl_vars['elementId']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['value']->value;?>
" <?php echo $_smarty_tpl->tpl_vars['options']->value;?>
 />
        <?php if (isset($_smarty_tpl->tpl_vars['description']->value)){?><p class="formElementDescription"><?php echo $_smarty_tpl->tpl_vars['description']->value;?>
</p><?php }?>
    </div>
</div>
<?php if (isset($_smarty_tpl->tpl_vars['suffix']->value)){?><?php echo $_smarty_tpl->tpl_vars['suffix']->value;?>
<?php }?>
<div class="clear"></div><?php }} ?>