<?php

use Functional\Either\Either;
use Functional\Either\No;
use Functional\Either\Yes;
use Functional\Maybe\Maybe;
use Functional\Maybe\Some;
use Functional\Result\ExceptionStack;
use Functional\Result\Failure;
use Functional\Result\Result;
use function Functional\Result\result;
use Functional\Result\Success;

final class DomainService {
    private $logger;

    public function withdrawMoney(string $accountId, int $amount): Result {
        // happy path execution
        $result = result(function() use ($accountId, $amount) {
            $accountId = new AccountId($accountId);

            /** @var Maybe $maybeAccount */
            $maybeAccount = $this->repository->load($accountId);
            if ($maybeAccount instanceof None) {
                throw new AccountNotFound($accountId);
            }

            /** @var Some $maybeAccount */
            /** @var Account $account */
            $account = $maybeAccount->extract();

            /** @var Either $wasAmountWithdrawn */
            $wasAmountWithdrawn = $account->withdraw($amount);

            $this->repository->save($account);

            return $wasAmountWithdrawn;
        })
        // Something wrong, we couldn't execute the operation. Let's handle it.
        ->fail(function(ExceptionStack $exceptionStack) use ($accountId, $amount) {
            $this->logger->error('Cannot withdraw amount',
                ['error' => $exceptionStack->toString(), 'account_id' => $accountId, 'amount' => $amount]
            );
        });

        // Happy path was successful. At this point, we may or not have withdrawn
        // any money depending on internal business rules.
        $result->ok(function(Either $wasAmountWithdrawn) use ($accountId, $amount) {
            // If we were able to, publish an event and log it.
            $wasAmountWithdrawn->yes(function() use ($accountId, $amount) {
                $this->publisher->publish(
                    new AmountWasWithdrawn($accountId, $amount)
                );
                $this->logger->info('Amount was withdrawn', ['account_id' => $accountId, 'amount' => $amount]);
            });
        })
        // While the happy path was executed successfully,
        // something went wrong while publishing the event or logging.
        // Let's log it.
        ->fail(function(ExceptionStack $exceptionStack) use ($accountId, $amount)  {
            $this->logger->warning(
                'Amount was withdrawn with error',
                ['error' => $exceptionStack->toString(), 'account_id' => $accountId, 'amount' => $amount]
            );
        });

        return $result;
    }
}

// How to use it
$service = new DomainService();
$result = $service->withdrawMoney('some_account_id', 1000);

switch (true) {
    case $result instanceof Success:
        /** @var Either $ */
        $wasAmountWithdrawn = $result->extract();

        switch (true) {
            case $wasAmountWithdrawn instanceof Yes:
                // ok, we did it
                break;
            case $wasAmountWithdrawn instanceof No:
                // nope, the service forbade it
                break;
        }

        break;
    case $result instanceof Failure:
        /** @var ExceptionStack $exceptionStack */
        $exceptionStack = $result->extract();
        break;
}
