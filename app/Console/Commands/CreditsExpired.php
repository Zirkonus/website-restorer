<?php

namespace App\Console\Commands;

use App\Transaction;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreditsExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credits:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expired credits on 31\'s day';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $transactions = Transaction::where('expired_at', '<', Carbon::now()->toDateString())
            ->where('expired_at', '!=', null)
            ->orderBy('user_id')
            ->get();

        $user_expired_credits = [];
        $user_id              = null;
        foreach ($transactions as $transaction) {
            if ($transaction->user_id != $user_id || $user_id === null) {
                $user_id                        = $transaction->user_id;
                $user_expired_credits[$user_id] = 0;
            }

            $user_expired_credits[$user_id] += $transaction->credits_left;
        }

        DB::transaction(function() use ($user_expired_credits) {
            foreach ($user_expired_credits as $id => $count) {
                $user = User::find($id);
                if (isset($user->id)) {
                    $user->credits = $user->credits - $count;
                    if ($user->credits < 0) {
                        $user->credits = 0;
                    }
                    $user->save();
                }
            }

            Transaction::where('expired_at', '<', Carbon::now()->toDateString())
                ->where('expired_at', '!=', null)
                ->orderBy('user_id')
                ->delete();

        });
    }
}