<?php

/**
 * GUN Application
 *
 * @author     Filip Sosnowski <filipsosnowski38@interia.eu>
 * @copyright  2013 Filip Sosnowski
 */

class public_rp_tickets_create extends ipsCommand
{
	public function doExecute( ipsRegistry $registry )
	{
		require_once( IPSLib::getAppDir( 'rp' ) . '/sources/tickets.php' );
		$this->registry->setClass( 'tickets', new tickets( $registry ) );
		$tickets = $this->registry->getClass( 'tickets' );
		
		$category = $tickets->fetchTicketCategory();
		$priority = $tickets->fetchTicketPriority();
		
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/editor/composite.php', 'classes_editor_composite' );
		$editor = new $classToLoad();
		$html = $editor->show( 'content', array( 'type'					=>	'mini',
												 'minimize'				=>	FALSE,
												 'height'				=>	148,
												 'autoSaveKey'			=>	'',
												 'warnInfo'				=>	NULL,
												 'modAll'				=>	FALSE,
												 'recover'				=>	FALSE,
												 'noSmilies'			=>	TRUE,
												 'isHtml'				=>	FALSE,
												 'isRte'				=>	NULL,
					 							 'isTypingCallBack'		=>	'',
												 'delayInit'			=>	FALSE,
												 'editorName'			=>	NULL ) );
												 
		if( isset( $this->request['send'] ) )
		{
			$tickets->createTicket( $this->request['title'], intval( $this->request['category'] ), intval( $this->request['priority'] ), $this->request['content'] );
		}
		
		$this->registry->output->setTitle( 'Zgłoszenia' );
		$this->registry->output->addNavigation( 'Zgłoszenia', 'app=rp&module=tickets' );
		$this->registry->output->addNavigation( 'Tworzenie nowego zgłoszenia', 'app=rp&module=tickets&section=create' );
		$this->registry->output->addContent( $this->registry->output->getTemplate( 'rp' )->tickets_create( $category, $priority, $html ) );
		$this->registry->output->sendOutput();
	}
}
