<?php

/**
 * GUN Application
 *
 * @author     Filip Sosnowski <filipsosnowski38@interia.eu>
 * @copyright  2013 Filip Sosnowski
 */

class tickets extends arrays
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
	
	public function fetchTickets( $member )
	{
		$this->DB->build( array( 'select'	=>	't.*, m.member_id, m.members_display_name, m.member_group_id',
								 'from'		=>	array( 'rp_tickets'			=>	't',
								 					   'members'			=>	'm' ),
								 'where'	=>	't.ticket_applicant = '. $member .' AND t.ticket_applicant = m.member_id',
								 'order'	=>	't.ticket_datetime DESC' ) );
		$this->DB->execute();
		
		while( $row = $this->DB->fetch() )
		{
			$category = $this->fetchTicketCategory();
			$priority = $this->fetchTicketPriority();
			$status = $this->fetchTicketStatus();
			
			$row['ticket_category']	= $category[ $row['ticket_category'] ];
			$row['ticket_priority']	= $priority[ $row['ticket_priority'] ];
			$row['ticket_status'] = $status[ $row['ticket_status'] ];
			
			$tickets[] = $row;
		}
		
		return $tickets;
	}
	
	public function fetchTicket( $ticket )
	{
		$this->DB->build( array( 'select'	=>	'*',
								 'from'		=>	'rp_tickets',
								 'where'	=>	'ticket_uid = '. $ticket .'' ) );
		$this->DB->execute();
		$row = $this->DB->fetch();
		
		$category = $this->fetchTicketCategory();
		$priority = $this->fetchTicketPriority();
		$status = $this->fetchTicketStatus();
			
		$row['ticket_category']	= $category[ $row['ticket_category'] ];
		$row['ticket_priority']	= $priority[ $row['ticket_priority'] ];
		$row['ticket_status'] = $status[ $row['ticket_status'] ];
		$row['applicant'] = IPSMember::load( $row['ticket_applicant'] );
			
		$ticket = $row;
		
		return $ticket;
	}
	
	public function fetchAnswers( $ticket )
	{
		IPSText::getTextClass( 'bbcode' )->parse_html = 1;
		IPSText::getTextClass( 'bbcode' )->parse_nl2br = 1;
		IPSText::getTextClass( 'bbcode' )->parse_bbcode = 1;
		IPSText::getTextClass( 'bbcode' )->parse_smilies = 1;
		
		$this->DB->build( array( 'select'	=>	'a.*, m.*',
								 'from'		=>	array( 'rp_tickets_answers'	=>	'a',
								 					   'members'			=>	'm' ),
								 'where'	=>	'a.answer_ticket = '. $ticket .' AND a.answer_author = m.member_id',
								 'order'	=>	'a.answer_uid ASC' ) );
		$this->DB->execute();
		
		while( $row = $this->DB->fetch() )
		{
			$row['answer_content'] = IPSText::getTextClass( 'bbcode' )->preDisplayParse( $row['answer_content'] );
			$answers[] = $row;
		}
		
		return $answers;
	}
	
	public function checkTicket( $ticket, $member )
	{
		$row = $this->DB->buildAndFetch( array( 'select'	=>	'ticket_uid',
												'from'		=>	'rp_tickets',
												'where'		=>	'ticket_uid = '. $ticket .' AND ticket_applicant = '. $member .'' ) );
		if( $this->DB->getTotalRows() )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	public function createAnswer( $ticket, $author, $content, $skip = FALSE )
	{
		if( $skip = FALSE )
		{
			if( strlen( $content ) > 5000 || strlen( $content ) < 8 )
			{
				$this->registry->output->showError( "Odpowiedź jest zbyt długa bądź krótka." );
			}
		
		}
			
		$this->DB->insert( 'rp_tickets_answers', array( 'answer_ticket'		=>	$ticket,
														'answer_author'		=>	$author,
														'answer_datetime'	=>	IPS_UNIX_TIME_NOW,
														'answer_content'	=>	$this->DB->addSlashes( $content ) ) );
														
		$this->DB->update( 'rp_tickets', array( 'ticket_update' => IPS_UNIX_TIME_NOW ), 'ticket_uid = '. $ticket .'' );
		
		$this->registry->output->redirectScreen( 'Odpowiedź została pomyślnie dodana.', $this->settings['base_url'] . 'app=rp&module=tickets&section=view&ticket='. $ticket .'' );
		
	}
	
	public function createTicket( $title, $category, $priority, $content )
	{	
		if( strlen( $title ) > 32 || strlen( $title ) < 4 || strlen( $content ) > 5000 || strlen( $content ) < 8 )
		{
			$this->registry->output->showError( "Tytuł zgłoszenia lub zawartość zgłoszenia jest zbyt długi(a) lub krótki(a)." );
		}
		
		if( ! array_key_exists( $category, $this->fetchTicketCategory() ) || ! array_key_exists( $priority, $this->fetchTicketPriority() ) )
		{
			$this->registry->output->showError( "Wybrany typ lub kategoria zgłoszenia nie jest prawidłowy(a)." );
		}
		
		$this->DB->insert( 'rp_tickets', array( 'ticket_applicant'	=>	$this->memberData['member_id'] ,
												'ticket_title'		=>	$this->DB->addSlashes( $title ),
												'ticket_category'	=>	$category,
												'ticket_priority'	=>	$priority,
												'ticket_datetime'	=>	IPS_UNIX_TIME_NOW ) );
		$ticket = $this->DB->getInsertId();
												
		$this->createAnswer( $ticket, $this->memberData['member_id'], $content );
												
		$this->registry->output->redirectScreen( 'Zgłoszenie zostało pomyślnie wysłane, czekaj na odpowiedź.', $this->settings['base_url'] . 'app=rp&module=tickets&section=list' );
		
	}
}

class arrays
{
	public function fetchTicketCategory()
	{
		return array( '1'	=>	'Nieokreślone',
					  '2'	=>	'Grupa',
					  '3'	=>	'Pojazd',
					  '4'	=>	'Drzwi',
					  '5'	=>	'Przedmiot',
					  '6'	=>	'Kara',
					  '7'	=>	'Strefa',
					  '8'	=>	'Obiekty',
					  '9'	=>	'Napis 3D',
					  '10'	=>	'Skrypt gry',
					  '11'	=>	'Skrypt forum',
					  '12'	=>	'Dział, temat',
					  '13'	=>	'Pytanie, pomoc',
					  '14'	=>	'Konto',
					  '15'	=>	'Postać',
					  '16'	=>	'Logi' );
	}
	
	public function fetchTicketPriority()
	{
		return array( '1'	=>	'Niski <img src="http://c-games.pl/public/style_images/community/ticket_low.gif">',
					  '2'	=>	'Średni <img src="http://c-games.pl/public/style_images/community/ticket_medium.gif">',
					  '3'	=>	'Wysoki <img src="http://c-games.pl/public/style_images/community/ticket_urgent.gif">' );
	}
	
	public function fetchTicketStatus()
	{
		return array( '1'	=>	'Oczekujący',
					  '2'	=>	'Rozpatrywany',
					  '3' 	=>	'Zamknięty');
	}
}
