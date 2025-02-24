<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\PostTransferRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

use DB;
use Auth;
use Str;
use Log;

use App\Jobs\ProcessTransfer;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;

class TransferController extends Controller
{
    public function getDetailById(int $id) {
        Try {
            $order = Transfer::find($id);
            if (!$order) {
                return $this->responseError('Invalid order', []);
            }

            if (!Gate::allows('view', $order)) {
                return $this->responseError('Permission denied', []);
            }

            return $this->responseSuccess('success', [
                'transaction_amount' => $order->amount,
                'status' => $order->status,
                'trans_date' => $order->created_at->format('c'),
            ]);
        } Catch (\Throwable $exception) {
            Log::error('Transfer Order Detail', [$exception->getMessage(), $exception->getFile(), $exception->getLine()]);
            return $this->responseError('Failed to submit the request', [], 500);
        }
    }

    public function postTransfer(PostTransferRequest $request)
    {
        Log::info('Transfer Order Params', $request->all());
        Try {
            $doc = Str::uuid();
            $user = User::where('email', $request->input('receiver_id'))
                ->first();

            if (!$user || ($user->id == Auth::user()->id)) {
                return $this->responseError('Invalid receiver');
            }

            $userId = Auth::user()->id;
            return DB::transaction(function () use ($request, $doc, $userId, $user) {
                $transferOrder = Transfer::create([
                    'doc_no' => $doc,
                    'user_id' => $userId,
                    'user_id_to' => $user->id,
                    'amount' => $request->input('amount'),
                    'remark' => "Transfer to {$user->email}"
                ]);
                if (!$transferOrder) {
                    return $this->responseError('Invalid order');
                }

                $resultOut = $this->transIn($transferOrder['id'], $userId, 0, $request->input('amount'), "Transfer to {$user->email}");
                if (!$resultOut['status']) {
                    return $this->responseError($resultOut['message'], []);
                }

                ProcessTransfer::dispatch($transferOrder->id);
                if (!$resultOut['status']) {
                    return $this->responseError($resultOut['message'], []);
                }

                return $this->responseSuccess('Order created successfully', [
                    'id' => $transferOrder->id
                ], 201);
            });
        } Catch (\Throwable $exception) {
            Log::error('Transfer Order Failed', [$exception->getMessage(), $exception->getFile(), $exception->getLine()]);
            return $this->responseError('Failed to submit the request', [], 500);
        }
    }

    public function transIn(int $transId, int $userId, float $amountIn = 0, float $amountOut = 0, string $remark = null)
    {
        $beforeBalance = $afterBalance = 0;
        $balance = User::where('id', $userId)
            ->lockForUpdate()
            ->first();
        if ($amountOut > 0) {
            if (!$balance || ($balance['balance'] < $amountOut)) {
                return [
                    'status' => false,
                    'message' => 'Insufficient balance'
                ];
            }
        }

        $beforeBalance = $balance['balance'];
        $result = Transaction::create([
            'user_id' => $userId,
            'transfer_id' => $transId,
            'amount_out' => $amountOut,
            'amount_in' => $amountIn,
            'status' => 'completed',
            'remark' => $remark ?? null
        ]);
        if ($result) {
            if ($amountIn > 0) {
                if ($balance) {
                    $balance['balance'] += $amountOut;
                    $balance->save();

                    $afterBalance = $balance['balance'];
                }
            }

            if ($amountOut > 0) {
                $balance['balance'] -= $amountOut;
                $balance->save();

                $afterBalance = $balance['balance'];
            }

            return [
                'status' => true,
                'message' => 'success',
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance
            ];
        }

        return [
            'status' => false,
            'message' => 'Failed to submit',
            'before_balance' => 0,
            'after_balance' => 0
        ];
    }
}
