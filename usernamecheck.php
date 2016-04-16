<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.usernamecheck
 *
 * @copyright   Copyright (C) 2005 - 2016 framontb. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Check if field 'username' (in com_user register form), meets some requirements:
 *   - Minimum number of characters (if no specified or 0, no limit apply).
 *   - Maximum number of characters (if no specified or 0, no limit apply).
 *   - username spelling compliant with user defined charset
 */
class PlgUserUsernameCheck extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app;
	
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	/**
	 * Method to handle the "onUserBeforeSave" event
	 *
	 * @param   array    $user   Holds the old user data.
	 * @param   boolean  $isnew  True if a new user is stored.
	 * @param   array    $data   Holds the new user data.
	 *
	 * @return  boolean $isNew   True to allow the save process to continue,
	 *			     false to stop it
	 *
	 * @since   3.6
	 */
	public function onUserBeforeSave($user, $isNew, $data)
	{
		// If we aren't saving a "new" user (registration), or if we are not 
		// in the front end of the site, the let the save happen withour interrption.
		if (!$this->app->isSite()) {
			return true;
		}
		
		// Result defaults to true
		$result = true;
		
		// Create the input object
		$input = JFactory::getApplication()->input;
		
		// Get the number of characters in $username
		$usernameLenght = StringHelper::strlen($data['username']);
		
		// CHECK MINIMUM CHARACTER'S NUMBER
		
		// Get the minimum number of characters
		$minNumChars = $this->params->get('minNumChars');
		
		// If is set minNumChars and $usernameLenght does't achieve minimum lenght
		if (($minNumChars) && ($usernameLenght < $minNumChars)){
			$this->app->enqueueMessage(JText::sprintf('PLG_USER_USERNAMECHECK_MINNUMCHARS_REQUIRED', $minNumChars, $data['username']), 'warning');
			$result = false;
		}
		
		// CHECK MAXIMUM CHARACTER'S NUMBER
		
		// Get the minimum number of characters
		$maxNumChars = $this->params->get('maxNumChars');
		
		// If is set maxNumChars and $usernameLenght surpass maximum lenght
		if (($maxNumChars) && ($usernameLenght > $maxNumChars)){
			$this->app->enqueueMessage(JText::sprintf('PLG_USER_USERNAMECHECK_MAXNUMCHARS_REQUIRED', $maxNumChars, $data['username']), 'warning');
			$result = false;
		}
		
		// CHECK IF USERNAME SPELLING IN CHARACTER SET
		
		// Get the charset specified in the plugin's parameters
		$charset = $this->params->get('charset');
		
		// Only check if $charset field is set
		if (!empty($charset)) {
			// Convert username to an array of characters
			$usernameSpelling = array_unique(StringHelper::str_split($data['username']));
			
			// Buffer for non-compliance characters
			$notAllowedCharsArray = array();
			
			// Walk the $usernameSpelling array looking for not allowed characters
			foreach ($usernameSpelling as $char) {
				// If the $char is not in charset, store it in buffer.
				if (StringHelper::strpos($charset, $char) === false) {
					$notAllowedCharsArray[] = $char;
				}
			}
			
			// If there are $chars not allowed, report it
			if (count($notAllowedCharsArray)) {
				$notAllowedCharsString = implode(" ", $notAllowedCharsArray);
				$this->app->enqueueMessage(JText::sprintf('PLG_USER_USERNAMECHECK_CHARSET_REQUIRED', $notAllowedCharsString), 'warning');
				$result = false;
			}
		}
		
		// Return result
		return $result;
		
	}
}