<?php /* Smarty version Smarty-3.1.7, created on 2012-01-13 09:35:52
         compiled from "/var/www/topdeals/htdocs/accounting/public/../application/accounting/view/template/form/submit.tpl.html" */ ?>
<?php /*%%SmartyHeaderCode:17428121454f0fece82cb6c5-23893225%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a093c160e1936873e890a97d81fa1a17c43a4dc4' => 
    array (
      0 => '/var/www/topdeals/htdocs/accounting/public/../application/accounting/view/template/form/submit.tpl.html',
      1 => 1326385934,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '17428121454f0fece82cb6c5-23893225',
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
  'unifunc' => 'content_4f0fece82ec33',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f0fece82ec33')) {function content_4f0fece82ec33($_smarty_tpl) {?><?php if (isset($_smarty_tpl->tpl_vars['prefix']->value)){?><?php echo $_smarty_tpl->tpl_vars['prefix']->value;?>
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