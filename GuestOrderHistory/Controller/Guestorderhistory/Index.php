<?php


namespace Born\OrderController\Controller\Guestorderhistory;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
	
    protected $orderCollectionFactory;
	
    protected $resultJsonFactory;
	
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
		$this->orderCollectionFactory = $orderCollectionFactory;
		$this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        //return $this->resultPageFactory->create();
		$result = $this->resultJsonFactory->create();
		$jsonData = $this->getJsonArrayOfGuestOrders();
		return $result->setData($jsonData);
    }
	

	public function getGuestOrderCollection()
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
		
		$totalGuestOrder = $this->getRequest()->getParam('total_guest_order');
		if ('all' !== $totalGuestOrder){
			$orderCollecion->getSelect()->limit((int)$totalGuestOrder);
		}
		
		return $orderCollecion;
	}

	public function getJsonArrayOfGuestOrders()
	{
		$jsonArray = [];
		$guestOrderCollection = $this->getGuestOrderCollection();
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
