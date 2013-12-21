<?php

/**
 * Role Play Application
 *
 * @author     Filip Sosnowski <filipsosnowski38@interia.eu>
 * @copyright  2013 Filip Sosnowski
 */
 
class public_rp_administration_list extends ipsCommand
{
	public function doExecute( ipsRegistry $registry )
	{
		require_once( IPSLib::getAppDir( 'rp' ) . '/sources/administration.php' );
		$this->registry->setClass( 'administration', new administration( $registry ) );
		$administration = $this->registry->getClass( 'administration' );
		
		$groups = $administration->fetchGroups();
		$members = $administration->fetchMembers();
		
		if( $this->request['do'] == 'removeGroup' )
		{
			$administration->removeGroup( intval( $this->request['group'] ) );
		}
		else if( $this->request['do'] == 'removeMember' )
		{
			$administration->removeMember( intval( $this->request['member'] ) );
		}
		
		if( isset( $this->request['createGroup'] ) )
		{
			$administration->createGroup( $this->request['groupName'] );
		}
		else if( isset( $this->request['addMember'] ) )
		{
			$administration->addMember( intval( $this->request['memberUid'] ) , $this->request['memberDuty'], intval( $this->request['memberGroup'] ) );
		}
		
		$this->registry->output->setTitle( 'Administracja' );
		$this->registry->output->addNavigation( 'Administracja', 'app=rp&module=administration' );
		$this->registry->output->addContent( $this->registry->output->getTemplate( 'rp' )->administration_list( $groups, $members ) );
		$this->registry->output->sendOutput();
	}
}
