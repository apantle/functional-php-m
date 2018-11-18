<?php

use Functional\Result\ExceptionStack;
use Functional\Result\Failure;
use Functional\Result\Result;
use function Functional\Result\result;
use Functional\Result\Success;

final class DomainService {
    private $logger;

    public function registerCustomer(string $customerId): Result {
        // happy path execution
        $result = result(function() use ($customerId) {
            $customerId = new CustomerId($customerId);
            $customer = new Customer($customerId);

            $this->repository->save($customer);

            return $customerId;
        })
        // Something wrong, we couldn't register the customer. Let's handle it.
        ->fail(function(ExceptionStack $exceptionStack) use ($customerId) {
            $this->logger->error('msg',
                ['error' => $exceptionStack->toString(), 'customer_id' => $customerId]
            );
        });

        // Happy path was successful, let's publish an event and log it
        $result->ok(function(CustomerId $customerId) {
            $this->publisher->publish(
                new CustomerWasRegistered($customerId)
            );
            $this->logger->info('Customer was registered', ['customer_id' => $customerId->toString()]);
        })
        // While the happy path was executed successfully,
        // something went wrong while publishing the event or logging.
        // Let's log it.
        ->fail(function(ExceptionStack $exceptionStack) use ($customerId)  {
            $this->logger->warning(
                'Customer was registered with error',
                ['error' => $exceptionStack->toString(), 'customer_id' => $customerId->toString()]
            );
        });

        return $result;
    }
}

// How to use it
$service = new DomainService();
$result = $service->registerCustomer('some_customer_id');

switch (true) {
    case $result instanceof Success:
        /** @var CustomerId $customerId */
        $customerId = $result->extract();
        break;
    case $result instanceof Failure:
        /** @var ExceptionStack $exceptionStack */
        $exceptionStack = $result->extract();
        break;
}
