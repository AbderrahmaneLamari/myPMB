<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: StatusProductsController.php,v 1.5 2022/10/21 08:02:19 qvarin Exp $
namespace Pmb\DSI\Controller;

use Pmb\DSI\Models\ProductStatus;

class StatusProductsController extends CommonController
{

	protected const VUE_NAME = "dsi/statusProducts";

	/**
	 * 
	 * {@inheritDoc}
	 * @see \Pmb\DSI\Controller\CommonController::getBreadcrumb()
	 */
	protected function getBreadcrumb()
	{
		global $msg;
		return "{$msg['dsi_statutes']} {$msg['menu_separator']} {$msg['dsi_products']}";
	}

	/**
	 * Ajout statut produit
	 */
	protected function addAction()
	{
		print $this->render([
			"status" => new ProductStatus()
		]);
	}

	/**
	 * Edition statut produit
	 */
	protected function editAction()
	{
		global $id;
		$id = intval($id);

		print $this->render([
			"status" => new ProductStatus($id)
		]);
	}

	/**
	 * Liste des statuts de produits
	 */
	protected function defaultAction()
	{
		$productStatus = new ProductStatus();
		print $this->render([
			"list" => $productStatus->getList()
		]);
	}
	
	public function save()
	{
		$this->data->id = intval($this->data->id);
		
		$diffusionStatus = new ProductStatus($this->data->id);
		$result = $diffusionStatus->check($this->data);
		if ($result['error']) {
			$this->ajaxError($result['errorMessage']);
			exit();
		}
		
		$diffusionStatus->setFromForm($this->data);
		if (0 == $this->data->id) {
			$diffusionStatus->create();
		} else {
			$diffusionStatus->update();
		}
		
		$this->ajaxJsonResponse($diffusionStatus);
		exit();
	}
	
	
	public function delete()
	{
		$diffusionStatus = new ProductStatus($this->data->id);
		$result = $diffusionStatus->delete();
		
		if ($result['error']) {
			$this->ajaxError($result['errorMessage']);
			exit();
		}
		$this->ajaxJsonResponse([
			'success' => true
		]);
		exit();
	}
}

