<?php

namespace Ascorak\Core\Model\Config\Source;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;

class CustomerGroup implements OptionSourceInterface
{
    /** @var array|null $options */
    protected ?array $options = null;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param DataObject $converter
     * @param SortOrderBuilder|null $sortOrderBuilder
     */
    public function __construct(
        protected GroupRepositoryInterface $groupRepository,
        protected SearchCriteriaBuilder $searchCriteriaBuilder,
        protected FilterBuilder $filterBuilder,
        protected DataObject $converter,
        protected ?SortOrderBuilder $sortOrderBuilder = null
    ) {
        $this->sortOrderBuilder = $sortOrderBuilder ?: ObjectManager::getInstance()
            ->get(SortOrderBuilder::class);
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return $this->getOptions();
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray(): array
    {
        $options = $this->getOptions();

        return array_reduce($options, function (array $accumulator, $option) {
            $accumulator[$option['value']] = $option['label'];
            return $accumulator;
        }, []);
    }

    /**
     * Get options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        if (is_array($this->options)) {
            return $this->options;
        }

        $groupAll[] = $this->filterBuilder
            ->setField(GroupInterface::ID)
            ->setConditionType('neq')
            ->setValue(GroupManagement::CUST_GROUP_ALL)
            ->create();
        $groupNameSortOrder = $this->sortOrderBuilder
            ->setField('customer_group_code')
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters($groupAll)
            ->addSortOrder($groupNameSortOrder)
            ->create();

        try {
            $groups = $this->groupRepository->getList($searchCriteria)->getItems();
        } catch (LocalizedException $e) {
            $groups = [];
        }

        $this->options = $this->converter->toOptionArray($groups, 'id', 'code');
        return $this->options;
    }
}
