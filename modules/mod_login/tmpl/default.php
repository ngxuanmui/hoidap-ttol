<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');

$facebook = new Facebook(array(
								'appId'  => '492429447479171',
								'secret' => '9925fea8d91e573f1a5d2a072c14c594',
					));

$user 	= JFactory::getUser();
$userFB = $facebook->getUser();
$onlyMerchant = false;

if (JRequest::getString('option') == 'com_merchant' && in_array(JRequest::getString('view'), array('signin', 'orders', 'deals'))) {
	$onlyMerchant = true;
}

if ($onlyMerchant && !$user->id) {
	return;
}

$isLogedIn 	= false;
$fbMe		= null;
if ($user->id) {
	$isLogedIn = true;
} else {
	if ($userFB) {
		try {
			// Proceed knowing you have a logged in user who's authenticated.
			$fbMe = $facebook->api('/me');
		} catch (FacebookApiException $e) {
			$userFB = null;
		}
	}
}

$logoutUrl= '';

if ($userFB) {
	$logoutUrl	= $facebook->getLogoutUrl();
} 

$loginUrl	= $facebook->getLoginUrl(
		array(
				'scope' => 'email, publish_stream',
				'redirect_uri' => JRoute::_(JURI::root() . 'index.php?option=com_users&task=registration.fbregistration', false)
		)
);


?>

<div id="fb-root"></div>
<script>(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=<?php echo CFG_FACEBOOK_API_ID ?>";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<?php if ($type == 'logout') : ?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form">
<?php if ($params->get('greeting')) : ?>
	<div class="login-greeting">
	<?php if($params->get('name') == 0) : {
		echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('name')));
	} else : {
		echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('username')));
	} endif; ?>
	</div>
<?php endif; ?>
	<div class="logout-button">
		<input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGOUT'); ?>" />
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.logout" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<?php else : ?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" >
	<?php if ($params->get('pretext')): ?>
		<div class="pretext">
		<p><?php echo $params->get('pretext'); ?></p>
		</div>
	<?php endif; ?>
	<fieldset class="userdata">
	<p id="form-login-username">
		<label for="modlgn-username"><?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?></label>
		<input id="modlgn-username" type="text" name="username" class="inputbox"  size="18" />
	</p>
	<p id="form-login-password">
		<label for="modlgn-passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
		<input id="modlgn-passwd" type="password" name="password" class="inputbox" size="18"  />
	</p>
	<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
	<p id="form-login-remember">
		<label for="modlgn-remember"><?php echo JText::_('MOD_LOGIN_REMEMBER_ME') ?></label>
		<input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>
	</p>
	<?php endif; ?>
	<input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGIN') ?>" />
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</fieldset>
	<ul>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
			<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
		</li>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
			<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
		</li>
		<?php
		$usersConfig = JComponentHelper::getParams('com_users');
		if ($usersConfig->get('allowUserRegistration')) : ?>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
				<?php echo JText::_('MOD_LOGIN_REGISTER'); ?></a>
		</li>
		<li>
			<div class="left"><a class="service" id="service-facebook" title="Đăng nhập qua tài khoản facebook" href="<?php echo $loginUrl ?>">Facebook</a></div>
		</li>
		<?php endif; ?>
	</ul>
	<?php if ($params->get('posttext')): ?>
		<div class="posttext">
		<p><?php echo $params->get('posttext'); ?></p>
		</div>
	<?php endif; ?>
</form>
<?php endif; ?>
