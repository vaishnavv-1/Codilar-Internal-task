<?php
namespace Codilar\AutoCustomer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Newsletter\Model\SubscriptionManagerInterface;

class CustomerSetup implements ObserverInterface
{
    private GroupRepositoryInterface $groupRepository;
    private CustomerRepositoryInterface $customerRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private SubscriptionManagerInterface $subscriptionManager;

    public function __construct(
        GroupRepositoryInterface $groupRepository,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubscriptionManagerInterface $subscriptionManager
    ) {
        $this->groupRepository = $groupRepository;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subscriptionManager = $subscriptionManager;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        
        // Auto-subscribe to newsletter
        $customerId = (int)$customer->getId();
        $storeId = (int)$customer->getStoreId();
        $this->subscriptionManager->subscribeCustomer($customerId, $storeId);
        
        // Get or create Website group
        $websiteGroup = $this->getWebsiteGroup();
        if ($websiteGroup->getId()) {
            $customer->setGroupId($websiteGroup->getId());
            $this->customerRepository->save($customer);
        }
    }

    private function getWebsiteGroup()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('customer_group_code', 'Website')
            ->create();
            
        $groups = $this->groupRepository->getList($searchCriteria)->getItems();
        
        if (!empty($groups)) {
            return reset($groups);
        }
        
        return null;
    }
}