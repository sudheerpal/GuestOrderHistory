<?php


namespace Born\OrderController\Model;

use Born\OrderController\Api\GuestorderInterface;

class Guestorder implements GuestorderInterface
{

    protected $orderCollectionFactory;
	

    public function __construct(
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
		$this->orderCollectionFactory = $orderCollectionFactory;
    }
	

    public function getGuestOrderHistory($param) {
		$arrayData = $this->getJsonArrayOfGuestOrders($param);
		return $arrayData;
    }
	

	public function getGuestOrderCollection($param)
	{
		$orderCollecion = $this->orderCollectionFactory
								->create()
								->addFieldToSelect('*');
								
		$orderCollecion->addFieldToFilter(
							'customer_id',
							array(
								'null' => true
							)
						);
						
		if ('all' !== $param){
			$orderCollecion->getSelect()->limit((int)$param);
		}
		
		return $orderCollecion;
	}
	

	public function getJsonArrayOfGuestOrders($param)
	{
		$jsonArray = [];
		$guestOrderCollection = $this->getGuestOrderCollection($param);
		foreach($guestOrderCollection as $_collection){
			$guestOrderHistory['status'] = $_collection->getStatus();
			$guestOrderHistory['total'] = $_collection->getGrandTotal();
			$allVisibleItems = $_collection->getAllVisibleItems();
			$itemArray = [];
			$qtyInvoiced = 0;
			foreach($allVisibleItems as $_item){
				$qtyInvoiced = $qtyInvoiced + $_item->getQtyInvoiced();
				$_itemArray['sku'] = $_item->getSku();
				$_itemArray['item_id'] = $_item->getItemId();
				$_itemArray['price'] = $_item->getRowTotal();
				$_itemArray['qty_invoiced'] = $_item->getQtyInvoiced();
				$_itemArray['qty'] = $_item->getQtyOrdered();
				$itemArray[] = $_itemArray;
			}
			$guestOrderHistory['qty_invoiced'] = $qtyInvoiced;
			$guestOrderHistory['item'] = $itemArray;
			$jsonArray[] = $guestOrderHistory;
		}
		return $jsonArray;
	}
}