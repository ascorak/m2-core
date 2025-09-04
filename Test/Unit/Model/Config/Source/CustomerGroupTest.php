<?php
namespace Ascorak\Core\Test\Unit\Model\Config\Source;

use Ascorak\Core\Model\Config\Source\CustomerGroup;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class CustomerGroupTest extends TestCase
{
    protected $groupMock;
    protected $searchResultsMock;
    protected $groupRepositoryMock;
    protected $searchCriteriaMock;
    protected $searchCriteriaBuilderMock;
    protected $filterMock;
    protected $filterBuilderMock;
    protected $converterMock;
    protected $sortOrderMock;
    protected $sortOrderBuilderMock;
    protected $source;

    protected $convertedOptions = [
        ['value' => 'test', 'label' => 'Test'],
        ['value' => 'test2', 'label' => 'Test 2']
    ];

    protected function setUp(): void
    {
        $this->groupMock = $this->getMockForAbstractClass(GroupInterface::class);

        $this->searchResultsMock = $this->getMockForAbstractClass(GroupSearchResultsInterface::class);
        $this->searchResultsMock->expects($this->any())
            ->method('getItems')
            ->willReturn([$this->groupMock]);

        $this->groupRepositoryMock = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getList'])
            ->getMockForAbstractClass();
        $this->groupRepositoryMock->expects($this->any())
            ->method('getList')
            ->willReturn($this->searchResultsMock);

        $this->searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->filterMock = $this->getMockForAbstractClass(Filter::class);

        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->filterBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->filterMock);

        $this->converterMock = $this->createMock(DataObject::class);
        $this->converterMock->expects($this->any())
            ->method('toOptionArray')
            ->with([$this->groupMock], 'id', 'code')
            ->willReturn($this->convertedOptions);

        $this->sortOrderMock = $this->getMockForAbstractClass(SortOrder::class);

        $this->sortOrderBuilderMock = $this->createMock(SortOrderBuilder::class);
        $this->sortOrderBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->sortOrderMock);

        $this->source = (new ObjectManager($this))->getObject(CustomerGroup::class, [
            'groupRepository' => $this->groupRepositoryMock,
            'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
            'filterBuilder' => $this->filterBuilderMock,
            'converter' => $this->converterMock,
            'sortOrderBuilder' => $this->sortOrderBuilderMock
        ]);
    }

    public function testToOptionArray(): void
    {
        $this->expectedForGetOptions();
        $this->assertSame($this->convertedOptions, $this->source->toOptionArray());
    }

    public function testToArray(): void
    {
        $expectedArray = [
            'test' => 'Test',
            'test2' => 'Test 2'
        ];
        $this->expectedForGetOptions();
        $this->assertSame($expectedArray, $this->source->toArray());
    }

    protected function expectedForGetOptions(): void
    {
        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setConditionType')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('create');

        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setAscendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create');

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilters')
            ->with([$this->filterMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($this->sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create');

        $this->groupRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock);

        $this->converterMock->expects($this->once())
            ->method('toOptionArray');
    }
}
