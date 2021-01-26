<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\Internal\DependencyManagement;

use ActionScheduler as ActionSchedulerCore;
use ActionScheduler_AsyncRequest_QueueRunner as QueueRunnerAsyncRequest;
use Automattic\WooCommerce\GoogleListingsAndAds\ActionScheduler\ActionScheduler;
use Automattic\WooCommerce\GoogleListingsAndAds\ActionScheduler\ActionSchedulerInterface;
use Automattic\WooCommerce\GoogleListingsAndAds\ActionScheduler\AsyncActionRunner;
use Automattic\WooCommerce\GoogleListingsAndAds\Exception\InvalidClass;
use Automattic\WooCommerce\GoogleListingsAndAds\Jobs\ActionSchedulerJobInterface;
use Automattic\WooCommerce\GoogleListingsAndAds\Jobs\ActionSchedulerJobMonitor;
use Automattic\WooCommerce\GoogleListingsAndAds\Jobs\DeleteAllProducts;
use Automattic\WooCommerce\GoogleListingsAndAds\Jobs\DeleteProducts;
use Automattic\WooCommerce\GoogleListingsAndAds\Jobs\JobInterface;
use Automattic\WooCommerce\GoogleListingsAndAds\Jobs\JobInitializer;
use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Service;
use Automattic\WooCommerce\GoogleListingsAndAds\Jobs\UpdateAllProducts;
use Automattic\WooCommerce\GoogleListingsAndAds\Jobs\UpdateProducts;
use Automattic\WooCommerce\GoogleListingsAndAds\Product\BatchProductHelper;
use Automattic\WooCommerce\GoogleListingsAndAds\Product\ProductHelper;
use Automattic\WooCommerce\GoogleListingsAndAds\Product\ProductRepository;
use Automattic\WooCommerce\GoogleListingsAndAds\Product\ProductSyncer;
use Automattic\WooCommerce\GoogleListingsAndAds\Product\SyncerHooks;

defined( 'ABSPATH' ) || exit;

/**
 * Class JobServiceProvider
 *
 * Provides the job classes and their related services to the container.
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\Internal\DependencyManagement
 */
class JobServiceProvider extends AbstractServiceProvider {

	/**
	 * @var array
	 */
	protected $provides = [
		JobInterface::class              => true,
		ActionSchedulerInterface::class  => true,
		AsyncActionRunner::class         => true,
		ActionSchedulerJobMonitor::class => true,
		JobInitializer::class            => true,
		SyncerHooks::class               => true,
		Service::class                   => true,
	];

	/**
	 * Use the register method to register items with the container via the
	 * protected $this->leagueContainer property or the `getLeagueContainer` method
	 * from the ContainerAwareTrait.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->share_with_tags(
			AsyncActionRunner::class,
			new QueueRunnerAsyncRequest( ActionSchedulerCore::store() ),
			ActionSchedulerCore::lock()
		);
		$this->share_with_tags( ActionScheduler::class, AsyncActionRunner::class );
		$this->share_with_tags( ActionSchedulerJobMonitor::class, ActionScheduler::class );

		$this->share_action_scheduler_job( UpdateAllProducts::class, ProductSyncer::class, ProductRepository::class );
		$this->share_action_scheduler_job( DeleteAllProducts::class, ProductSyncer::class, BatchProductHelper::class, ProductRepository::class );
		$this->share_action_scheduler_job( UpdateProducts::class, ProductSyncer::class, ProductRepository::class );
		$this->share_action_scheduler_job( DeleteProducts::class, ProductSyncer::class );

		$this->share_with_tags(
			JobInitializer::class,
			JobInterface::class
		);

		$this->share_with_tags(
			SyncerHooks::class,
			BatchProductHelper::class,
			ProductHelper::class,
			UpdateProducts::class,
			DeleteProducts::class
		);
	}

	/**
	 * Share an ActionScheduler job class
	 *
	 * @param string $class         The class name to add.
	 * @param mixed  ...$arguments  Constructor arguments for the class.
	 *
	 * @throws InvalidClass When the given class does not implement the ActionSchedulerJobInterface.
	 */
	protected function share_action_scheduler_job( string $class, ...$arguments ) {
		$implements = class_implements( $class );
		if ( ! array_key_exists( ActionSchedulerJobInterface::class, $implements ) ) {
			throw InvalidClass::should_implement( $class, ActionSchedulerJobInterface::class );
		}

		$this->share_with_tags(
			$class,
			ActionScheduler::class,
			ActionSchedulerJobMonitor::class,
			...$arguments
		);
	}
}
