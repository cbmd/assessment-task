<?php

namespace Assessment\Tasks\Console\Command;

use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory;
use Magento\Review\Model\ResourceModel\Review\Status\Collection as StatusCollection;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReviewListCommand
 *
 * @package Assessment\Tasks\Console\Command
 */
class ReviewListCommand extends Command
{
    /**
     *  ProductID Argument Constant
     */
    const PRODUCT_ID_ARGUMENT = 'product_id';

    /**
     * @var CollectionFactory
     */
    private $productReviewFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ReviewListCommand constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory     $productReviewFactory
     * @param string|null           $name
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StatusCollection $statusCollection,
        CollectionFactory $productReviewFactory,
        $name = null
    ) {

        parent::__construct($name);
        $this->productReviewFactory = $productReviewFactory;
        $this->storeManager = $storeManager;
        $this->statusCollection = $statusCollection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('review:list')
            ->setDescription('Display reviews for specific product id')
            ->addArgument(
                self::PRODUCT_ID_ARGUMENT,
                InputArgument::REQUIRED,
                'Product entity id to list review for'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $collection \Magento\Review\Model\ResourceModel\Review\Product\Collection */
        $collection = $this->productReviewFactory->create();
        $allStoreIds = \array_keys($this->storeManager->getStores(true));
        $statusCodes = $this->statusCollection->toOptionArray();


        $collection
            ->addEntityFilter($input->getArgument(self::PRODUCT_ID_ARGUMENT))
            ->setStoreFilter($allStoreIds);

        /** @var \Symfony\Component\Console\Helper\TableHelper $table */
        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(['Status', 'Date', 'Detail', 'Nickname']);

        foreach ($collection as $review) {
            $table->addRow([
                $statusCodes[$review->getStatusId()]['label'] ?? $review->getStatusId(),
                $review->getUpdatedAt(),
                $review->getDetail(),
                $review->getDetail()
            ]);
        }

        $table->render($output);
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(
        InputInterface $input,
        OutputInterface $output
    ) {
        $productId = $input->getArgument(self::PRODUCT_ID_ARGUMENT);

        if (!\is_numeric($productId)) {
            $output->writeln("<error>Product ID should be a number</error>");
            throw new \InvalidArgumentException('Parameter validation failed');
        }
    }
}
