<?php /* Smarty version Smarty-3.1.7, created on 2012-01-13 08:13:04
         compiled from "/var/www/topdeals/htdocs/accounting/public/../application/accounting/view/template/layout.tpl.html" */ ?>
<?php /*%%SmartyHeaderCode:19756658684f0fd980a618f9-00505714%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c875b4ba1cb936e2b3090646384dd8716756a3d7' => 
    array (
      0 => '/var/www/topdeals/htdocs/accounting/public/../application/accounting/view/template/layout.tpl.html',
      1 => 1326385934,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '19756658684f0fd980a618f9-00505714',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'headTitle' => 0,
    'css' => 0,
    'jsHeader' => 0,
    'WEB_ROOT' => 0,
    'CONTENT' => 0,
    'applicationEnv' => 0,
    'debugContent' => 0,
    'jsFooter' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f0fd980a9c4c',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f0fd980a9c4c')) {function content_4f0fd980a9c4c($_smarty_tpl) {?><!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <title><?php echo $_smarty_tpl->tpl_vars['headTitle']->value;?>
</title>
        <?php if (isset($_smarty_tpl->tpl_vars['css']->value)){?><?php echo $_smarty_tpl->tpl_vars['css']->value;?>
<?php }?>
        <?php if (isset($_smarty_tpl->tpl_vars['jsHeader']->value)){?><?php echo $_smarty_tpl->tpl_vars['jsHeader']->value;?>
<?php }?>
        <script>document.documentElement.className += ' js';</script>
        <!--[if lt IE 9]><script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    </head>
    <body>
        <header>
            <h1><a href="<?php echo $_smarty_tpl->tpl_vars['WEB_ROOT']->value;?>
">Topdeals Buchhaltung</a></h1>
            <nav>
                <ul>
                    <li><a href="<?php echo $_smarty_tpl->tpl_vars['WEB_ROOT']->value;?>
">&Uuml;bersicht</a></li>
                    <li><a href="<?php echo $_smarty_tpl->tpl_vars['WEB_ROOT']->value;?>
/campaign">Kampagnen</a></li>
                    <li><a href="<?php echo $_smarty_tpl->tpl_vars['WEB_ROOT']->value;?>
/group">Gruppen</a></li>
                </ul>
            </nav>
        </header>
        <section role="main">
                <?php echo $_smarty_tpl->tpl_vars['CONTENT']->value;?>

        </section>
        <?php if ($_smarty_tpl->tpl_vars['applicationEnv']->value=='development'){?>
        <section role="debug">
            <h3>Debug Output</h3>
            <?php echo $_smarty_tpl->tpl_vars['debugContent']->value;?>

        </section>
        <?php }?>

        <?php if (isset($_smarty_tpl->tpl_vars['jsFooter']->value)){?><?php echo $_smarty_tpl->tpl_vars['jsFooter']->value;?>
<?php }?>
    </body>
</html>
<?php }} ?>