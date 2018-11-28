<?php
/**
 * Created by PhpStorm.
 * User: medic84
 * Date: 19.10.18
 * Time: 10:48
 */

namespace SmartCat\Connector\Magento\Ui\Component\Listing\Column;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use SmartCat\Connector\Magento\Model\Config\Source\WorkflowStagesList;

class WorkFlowColumn extends Column
{
    /** @var WorkflowStagesList */
    protected $translation;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param WorkflowStagesList $translation
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        WorkflowStagesList $translation,
        array $components = [],
        array $data = []
    ) {
        $this->translation = $translation;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if($this->getData('name') =='stages'){
                    try {
                        $codes = explode(',', $item[$this->getData('name')]);
                        foreach ($codes as &$code) {
                            $index = array_search($code, array_column($this->translation->toOptionArray(), 'value'));
                            $code = $this->translation->toOptionArray()[$index]['label'];
                        }
                        $item[$this->getData('name')] = implode(', ', $codes);
                    } catch (NoSuchEntityException $e) {
                        continue;
                    }

                }
            }
        }

        return $dataSource;
    }
}