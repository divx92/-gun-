<?php

/**
 * GUN Application
 *
 * @author     Filip Sosnowski <filipsosnowski38@interia.eu>
 * @copyright  2013 Filip Sosnowski
 */

class administration
{
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $memberData;
	
	public function __construct( ipsRegistry $registry )
	{
		$this->registry		=  $registry;
		$this->DB			=  $this->registry->DB();
		$this->settings		=& $this->registry->fetchSettings();
		$this->request		=& $this->registry->fetchRequest();
		$this->memberData	=& $this->registry->member()->fetchMemberData();
	}
	
	public function fetchGroups()
	{
		$this->DB->build( array( 'select'	=>	'*',
								 'from'		=>	'rp_administration_groups' ) );
		$this->DB->execute();
		
		while( $row = $this->DB->fetch() )
		{
			$groups[] = $row;
		}
		
		return $groups;
	}
	
	public function fetchMembers()
	{
		$this->DB->build( array( 'select'	=>	'a.*, m.member_id as m_id, m.members_display_name',
								 'from'		=>	array( 'rp_administration_members'	=>	'a',
								 					   'members'					=>	'm' ),
								 'where'	=>	'a.member_id = m.member_id' ) );
		$this->DB->execute();
		
		while( $row = $this->DB->fetch() )
		{
			$members[] = $row;
		}
		
		return $members;
	}
	
	public function removeMember( $member )
	{
		if( $this->memberData['member_group_id'] != 4 )
		{
			$this->registry->output->showError( 'Nie posiadasz odpowiednich uprawnień żeby to wykonać.' );
		}
		
		$this->DB->delete( 'rp_administration_members', 'member_id = '. $member .'' );
		$this->registry->output->redirectScreen( 'Użytkownik został pomyślnie usunięty.', $this->settings['base_url'] . 'app=rp&module=administration&section=list' );
	}
	
	public function removeGroup( $group )
	{
		if( $this->memberData['member_group_id'] != 4 )
		{
			$this->registry->output->showError( 'Nie posiadasz odpowiednich uprawnień żeby to wykonać.' );
		}
		
		$this->DB->delete( 'rp_administration_groups', 'group_uid = '. $group .'' );
		$this->registry->output->redirectScreen( 'Grupa została pomyślnie usunięta.', $this->settings['base_url'] . 'app=rp&module=administration&section=list' );
	}
	
	public function createGroup( $name )
	{
		if( $this->memberData['member_group_id'] != 4 )
		{
			$this->registry->output->showError( 'Nie posiadasz odpowiednich uprawnień żeby to wykonać.' );
		}
		
		if( strlen( $name ) > 25 )
		{
			$this->registry->output->showError( 'Nazwa grupy jest zbyt długa. Maksymalnie 25 znaków.' );
		}
		
		$this->DB->insert( 'rp_administration_groups', array( 'group_name'	=>	$this->DB->addSlashes( $name ) ) );
		$this->registry->output->redirectScreen( 'Grupa została pomyślnie stworzona.', $this->settings['base_url'] . 'app=rp&module=administration&section=list' );
		
	}
	
	public function addMember( $member, $duty, $group )
	{
		if( $this->memberData['member_group_id'] != 4 )
		{
			$this->registry->output->showError( 'Nie posiadasz odpowiednich uprawnień żeby to wykonać.' );
		}
		
		if( strlen( $duty ) > 100 )
		{
			$this->registry->output->showError( 'Nazwa obowiązku jest zbyt długa. Maksymalnie 100 znaków.' );			
		}
		
		$this->DB->insert( 'rp_administration_members', array( 'member_id'		=>	$member,
															   'member_duty'	=>	$this->DB->addSlashes( $duty ),
															   'member_group'	=>	$group ) );
		$this->registry->output->redirectScreen( 'Użytkownik został pomyślnie dodany do grupy.', $this->settings['base_url'] . 'app=rp&module=administration&section=list' );														   
	}
}
