<?php
/**
 *  @package AkeebaSubs
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AkeebasubsControllerCpanels extends F0FController
{
	public function execute($task) {
		if(!in_array($task, array('browse','hide2copromo', 'wizardstep', 'updateinfo'))) {
			$task = 'browse';
		}
		parent::execute($task);
	}

	protected function onBeforeBrowse() {
		$result = parent::onBeforeBrowse();

		if($result) {
			F0FModel::getTmpInstance('Cpanels', 'AkeebasubsModel')
				->checkAndFixDatabase()
				->saveMagicVariables();

			// Run the automatic update site refresh
			/** @var AkeebasubsModelUpdates $updateModel */
			$updateModel = F0FModel::getTmpInstance('Updates', 'AkeebasubsModel');
			$updateModel->refreshUpdateSite();
		}

		return $result;
	}

	public function hide2copromo()
	{
		// Fetch the component parameters
		$db = JFactory::getDbo();
		$sql = $db->getQuery(true)
			->select($db->qn('params'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('element').' = '.$db->q('com_akeebasubs'));
		$db->setQuery($sql);
		$rawparams = $db->loadResult();
		$params = new JRegistry();
		$params->loadString($rawparams, 'JSON');

		// Set the show2copromo parameter to 0
		$params->set('show2copromo', 0);

		// Save the component parameters
		$data = $params->toString('JSON');
		$sql = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params').' = '.$db->q($data))
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('element').' = '.$db->q('com_akeebasubs'));

		$db->setQuery($sql);
		$db->execute();

		// Redirect back to the control panel
		$url = '';
		$returnurl = $this->input->getBase64('returnurl', '');
		if(!empty($returnurl)) {
			$url = base64_decode($returnurl);
		}
		if(empty($url)) {
			$url = JURI::base().'index.php?option=com_akeebasubs';
		}
		$this->setRedirect($url);
	}

	public function wizardstep()
	{
		$wizardstep = (int)$this->input->getInt('wizardstep', 0);

		// Fetch the component parameters
		$db = JFactory::getDbo();
		$sql = $db->getQuery(true)
			->select($db->qn('params'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('element').' = '.$db->q('com_akeebasubs'));
		$db->setQuery($sql);
		$rawparams = $db->loadResult();
		$params = new JRegistry();
		$params->loadString($rawparams, 'JSON');

		// Set the wzardstep parameter to whatever we were told to
		$params->set('wizardstep', $wizardstep);

		// Save the component parameters
		$data = $params->toString('JSON');
		$sql = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params').' = '.$db->q($data))
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('element').' = '.$db->q('com_akeebasubs'));

		$db->setQuery($sql);
		$db->execute();

		// Redirect back to the control panel
		$url = '';
		$returnurl = $this->input->getBase64('returnurl', '');
		if(!empty($returnurl)) {
			$url = base64_decode($returnurl);
		}
		if(empty($url)) {
			$url = JURI::base().'index.php?option=com_akeebasubs';
		}
		$this->setRedirect($url);
	}

	public function updateinfo()
	{
		/** @var AkeebasubsModelUpdates $updateModel */
		$updateModel = F0FModel::getTmpInstance('Updates', 'AkeebasubsModel');
		$updateInfo = (object)$updateModel->getUpdates();

		$result = '';

		if ($updateInfo->hasUpdate)
		{
			$strings = array(
				'header'		=> JText::sprintf('COM_AKEEBASUBS_CPANEL_MSG_UPDATEFOUND', $updateInfo->version),
				'button'		=> JText::sprintf('COM_AKEEBASUBS_CPANEL_MSG_UPDATENOW', $updateInfo->version),
				'infourl'		=> $updateInfo->infoURL,
				'infolbl'		=> JText::_('COM_AKEEBASUBS_CPANEL_MSG_MOREINFO'),
			);

			$result = <<<ENDRESULT
	<div class="alert alert-warning">
		<h3>
			<span class="icon icon-exclamation-sign glyphicon glyphicon-exclamation-sign"></span>
			{$strings['header']}
		</h3>
		<p>
			<a href="index.php?option=com_installer&view=update" class="btn btn-primary">
				{$strings['button']}
			</a>
			<a href="{$strings['infourl']}" target="_blank" class="btn btn-small btn-info">
				{$strings['infolbl']}
			</a>
		</p>
	</div>
ENDRESULT;
		}

		echo '###' . $result . '###';

		// Cut the execution short
		JFactory::getApplication()->close();
	}
}