<?php

/**
 * GUN Application
 *
 * @author     Filip Sosnowski <filipsosnowski38@interia.eu>
 * @copyright  2013 Filip Sosnowski
 */

class public_rp_tickets_view extends ipsCommand
{
	public function doExecute( ipsRegistry $registry )
	{
		require_once( IPSLib::getAppDir( 'rp' ) . '/sources/tickets.php' );
		$this->registry->setClass( 'tickets', new tickets( $registry ) );
		$tickets = $this->registry->getClass( 'tickets' );
		
		if( ! $tickets->checkTicket( intval( $this->request['ticket'] ), $this->memberData['member_id'] ) || $this->memberData['member_group_id'] != 4 )
		{
			$this->registry->output->showError( 'Zgłoszenie nie istnieje, lub nie jest napisane przez Ciebie.' );
		}
		
		$ticket = $tickets->fetchTicket( intval( $this->request['ticket'] ) );
		$answers = $tickets->fetchAnswers( intval( $this->request['ticket'] ) );
		
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
												 
		if( isset( $this->request['add'] ) )
		{
			$tickets->createAnswer( intval( $this->request['ticket'] ), $this->memberData['member_id'], $this->request['content'], $skip = FALSE );
		}
		
		$this->registry->output->setTitle( 'Zgłoszenia' );
		$this->registry->output->addNavigation( 'Zgłoszenia', 'app=rp&module=tickets' );
		$this->registry->output->addNavigation( ''. $ticket['ticket_title'] .' (UID: '. $ticket['ticket_uid'] .')', 'app=rp&module=tickets&section=view&ticket='. $ticket['ticket_uid'] .'' );
		$this->registry->output->addContent( $this->registry->output->getTemplate( 'rp' )->tickets_view( $ticket, $answers, $html ) );
		$this->registry->output->sendOutput();
	}
}
