<?php
class ActionsDiscount4lines
{

	function __construct($db)
	{
		global $langs;

		$this->db = $db;
		$langs->load('discount4lines@discount4lines');
	}

	/** Overloading the formConfirm function : replacing the parent's function with the one below
	 * @param      $parameters  array           meta datas of the hook (context, etc...)
	 * @param      $object      CommonObject    the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param      $action      string          current action (if set). Generally create or edit or null
	 * @param      $hookmanager HookManager     current hook manager
	 * @return     void
	 */
	function formConfirm($parameters, &$object, &$action, $hookmanager)
	{
		global $langs,$db,$user, $conf;
		
		$error = 0;

		$langs->load('discount4lines@discount4lines');

		$contexts = explode(':',$parameters['context']);

		if(in_array('propalcard',$contexts) || in_array('invoicecard',$contexts)) {

			if ($object->statut == 0  && $user->rights->{$object->element}->creer) {
				
				if($action == 'ask_discount4lines') {
					$form = new Form($this->db);
					
					$actionform = 'discount4lines';
					$title = $langs->trans('ApplyDiscount4linesTitle');
					$question = '';
					$formquestion = array(
						array('label'=> $langs->trans('EnterDiscountToApplyToEachLines'), 'name' => 'amount_discount4lines', 'type' => 'text', 'size' => 3)
					);
					$selectedchoice = 'yes';
					$useajax = 1;
					$out = $form->formconfirm($_SERVER['PHP_SELF'].'?ref='.$object->ref, $title, $question, $actionform, $formquestion, $selectedchoice, $useajax);
					
					if (! $error)
					{
						$this->results = array();
						$this->resprints = $out;
						
						return 0; // or return 1 to replace standard code
					}
					else
					{
						$this->errors[] = 'Error message';
						return -1;
					}
				}
			
				
				
			}
		}
	}
	
	/** Overloading the doActions function : replacing the parent's function with the one below
	 * @param      $parameters  array           meta datas of the hook (context, etc...)
	 * @param      $object      CommonObject    the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param      $action      string          current action (if set). Generally create or edit or null
	 * @param      $hookmanager HookManager     current hook manager
	 * @return     void
	 */
	function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $langs,$db,$user, $conf;
	
		$langs->load('discount4lines@discount4lines');
	
		$contexts = explode(':',$parameters['context']);
	
		if( in_array('propalcard',$contexts) || in_array('invoicecard',$contexts)) {
	
			if ($object->statut == 0  && $user->rights->{$object->element}->creer) {
				
				if($action == 'discount4lines') {
					
					$countLineUpdated = 0;
					$err = 0;
					
					foreach($object->lines as $line) {
						$remise_percent = GETPOST('amount_discount4lines','int');
						if($line->total_ht > 0) {
							
							if(in_array('propalecard',$contexts)) {
							
								$res = $object->updateline(
									$line->id,
									$line->subprice,
									$line->qty,
									$remise_percent,
									$line->tva_tx,
									$line->localtax1_tx,
									$line->localtax2_tx,
									$line->desc,
									$line->price_base_type,
									$line->infobits,
									$line->special_code,
									$line->fk_parent_line,
									$line->skip_update_total,
									$line->fk_fournprice,
									$line->pa_ht,
									$line->label,
									$line->product_type,
									$line->date_start,
									$line->date_end,
									$line->array_options,
									$line->fk_unit
								);
							} elseif(in_array('invoicecard',$contexts)) {
								$res = $object->updateline(
									$line->id, 
									$line->desc, 
									$line->subprice, 
									$line->qty, 
									$remise_percent, 
									$line->date_start, 
									$line->date_end, 
									$line->tva_tx, 
									$line->localtax1_tx, 
									$line->localtax2_tx, 
									$line->price_base_type, 
									$line->infobits, 
									'', // type 
									$line->fk_parent_line, 
									$line->skip_update_total, 
									$line->fk_fournprice, 
									$line->pa_ht=0, 
									$line->label, 
									$line->special_code, 
									$line->array_options,
									$line->situation_percent,
									$line->fk_unit
								);
							}
		
							if($res > 0) {
								$countLineUpdated++;
							} else {
								$err++;
							}
						}
					}
					
					if($countLineUpdated > 0) {
						setEventMessage($langs->trans('Discount4linesApplied', $countLineUpdated));
					}
				}
			}
		}
	}
	
	
			

	function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager) {
		
		global $langs,$db,$user, $conf;
		
		$out = '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=ask_discount4lines">' . $langs->trans('BtnDiscount4Lines') . '</a></div>';
		
		
		if (! $error)
		{
			$this->results = array();
			$this->resprints = $out;
			print $out;
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
}