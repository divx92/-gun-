<?php

/**
 * GUN Application
 *
 * @author     Filip Sosnowski <filipsosnowski38@interia.eu>
 * @copyright  2013 Filip Sosnowski
 */
 
class public_game_tickets_list extends ipsCommand
{
	public function doExecute( ipsRegistry $registry )
	{
		require_once( IPSLib::getAppDir( 'game' ) . '/sources/tickets.php' );
		$this->registry->setClass( 'tickets', new tickets( $registry ) );
		$tickets = $this->registry->getClass( 'tickets' );
		
		$tickets = $tickets->fetchTickets( $this->memberData['member_id'] );
		
		$this->registry->output->setTitle( 'Zgłoszenia' );
		$this->registry->output->addNavigation( 'Zgłoszenia', 'app=rp&module=tickets' );
		$this->registry->output->addContent( $this->registry->output->getTemplate( 'game' )->tickets_list( $tickets ) );
		$this->registry->output->sendOutput();
	}
}
