<?php

namespace Vtlabs\Core\Http\Controllers\Api\Admin;

use Carbon\Carbon;
use Depsimon\Wallet\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Core\Models\User\User;

class DashboardController extends Controller
{
    public function userAnalytics(Request $request)
    {
        $request->validate([
            'period' => 'required|in:week,month,year,all'
        ]);

        $days = 7;
        $period = $request->period;
        $groupBy = "DATE('created_at')";
        $label = 'DATE(created_at) as created_at';

        switch ($period) {
            case 'week':
                $days = 7;
                $groupBy = 'DATE(created_at)';
                $label = 'DATE(created_at) as created_at';
                break;
            case 'month':
                $days = 30;
                $groupBy = 'DATE(created_at)';
                $label = 'DATE(created_at) as created_at';
                break;
            case 'year':
                $days = 365;
                $groupBy = 'MONTH(created_at)';
                $label = 'MONTH(created_at) as created_at';
                break;
            case 'all':
                $days = 365 * 5;
                $groupBy = 'YEAR(created_at)';
                $label = 'YEAR(created_at) as created_at';
                break;
        }

        $usersChartData = DB::table('users')->select(DB::raw($label), DB::raw('count(*) as total'))
            ->whereDate('created_at', '>', Carbon::now()->subDays($days))
            ->whereDate('created_at', '<=', Carbon::now())
            ->groupBy(DB::raw($groupBy));

        $usersChartData = $usersChartData->get();

        $summary = [
            ["title" => "Total", "value" => User::whereRaw("1=1")->count()],
            ["title" => "Last Month", "value" => User::whereDate('created_at', '>', Carbon::now()->subDays(30))->count()],
            ["title" => "Last Week", "value" => User::whereDate('created_at', '>', Carbon::now()->subDays(7))->count()],
            ["title" => "Today", "value" => User::whereDate('created_at', '=', Carbon::now())->count()]
        ];

        $chartLabel = array_map(function ($date) use ($period) {
            return $this->formatUserDataDate($date, $period);
        }, $usersChartData->pluck('created_at')->toArray());

        return response()->json([
            "chart" => [
                "chartLabel" => $chartLabel,
                "linesData" => [
                    $usersChartData->pluck('total')
                ]
            ],
            "summary" => $summary
        ]);
    }

    public function transactionAnalytics(Request $request)
    {
        $request->validate([
            'period' => 'required|in:week,month,year,all'
        ]);

        $days = 7;
        $period = $request->period;
        $groupBy = "DATE('created_at')";
        $label = 'DATE(created_at) as created_at';

        switch ($period) {
            case 'week':
                $days = 7;
                $groupBy = 'DATE(created_at)';
                $label = 'DATE(created_at) as created_at';
                break;
            case 'month':
                $days = 30;
                $groupBy = 'DATE(created_at)';
                $label = 'DATE(created_at) as created_at';
                break;
            case 'year':
                $days = 365;
                $groupBy = 'MONTH(created_at)';
                $label = 'MONTH(created_at) as created_at';
                break;
            case 'all':
                $days = 365 * 5;
                $groupBy = 'YEAR(created_at)';
                $label = 'YEAR(created_at) as created_at';
                break;
        }

        $usersChartData = DB::table('wallet_transactions')->select(DB::raw($label), DB::raw('sum(amount) as total'))
            ->whereDate('created_at', '>', Carbon::now()->subDays($days))
            ->whereDate('created_at', '<=', Carbon::now())
            ->where('type', 'deposit')
            ->groupBy(DB::raw($groupBy));

        $usersChartData = $usersChartData->get();

        $summary = [
            ["title" => "Total", "value" => Transaction::whereRaw("1=1")->sum('amount')],
            ["title" => "Last Month", "value" => Transaction::whereDate('created_at', '>', Carbon::now()->subDays(30))->where('type', 'deposit')->sum('amount')],
            ["title" => "Last Week", "value" => Transaction::whereDate('created_at', '>', Carbon::now()->subDays(7))->where('type', 'deposit')->sum('amount')],
            ["title" => "Today", "value" => Transaction::whereDate('created_at', '=', Carbon::now())->where('type', 'deposit')->sum('amount')]
        ];

        $chartLabel = array_map(function ($date) use ($period) {
            return $this->formatUserDataDate($date, $period);
        }, $usersChartData->pluck('created_at')->toArray());

        return response()->json([
            "chart" => [
                "chartLabel" => $chartLabel,
                "linesData" => [
                    $usersChartData->pluck('total')
                ]
            ],
            "summary" => $summary
        ]);
    }

    public function activityAnalytics(Request $request)
    {
        $request->validate([
            'period' => 'required|in:week,month,year,all'
        ]);

        $days = 7;
        $period = $request->period;
        $groupBy = "DATE('created_at')";
        $label = 'DATE(created_at) as created_at';

        switch ($period) {
            case 'week':
                $days = 7;
                $groupBy = 'DATE(created_at)';
                $label = 'DATE(created_at) as created_at';
                break;
            case 'month':
                $days = 30;
                $groupBy = 'DATE(created_at)';
                $label = 'DATE(created_at) as created_at';
                break;
            case 'year':
                $days = 365;
                $groupBy = 'MONTH(created_at)';
                $label = 'MONTH(created_at) as created_at';
                break;
            case 'all':
                $days = 365 * 5;
                $groupBy = 'YEAR(created_at)';
                $label = 'YEAR(created_at) as created_at';
                break;
        }

        $activityData = DB::table('activity_logs')->select(DB::raw($label), DB::raw('count(*) as total'))
            ->whereDate('created_at', '>', Carbon::now()->subDays($days))
            ->whereDate('created_at', '<=', Carbon::now())
            ->orderBy('created_at', 'desc')
            ->groupBy(DB::raw($groupBy));

        $activityData = $activityData->get();

        $response = [];
        $i = 0;

        foreach ($activityData as $data) {
            $yesterday = $activityData[$i + 1]->total ?? 0;
            $today = $data->total;

            $percentChange =  $today > 0 ? (($yesterday - $today) / $today) * 100 : ($yesterday > 0 ? -100 : 0);
            $percentChange = round($percentChange, 2);

            $response[] = [
                'date' => $this->formatActivityDataDate($data->created_at, $period),
                'pagesVisitCount' => $data->total,
                'deltaUp' => $today > $yesterday,
                'newVisits' => abs($percentChange)
            ];

            $i++;
        }

        return response()->json($response);
    }

    function formatActivityDataDate($date, $period)
    {
        if ($period == 'week') {
            $in = date_create($date);
            return date_format($in, "D");
        }
        if ($period == 'month') {
            $in = date_create($date);
            return date_format($in, "d");
        }
        if ($period == 'year') {
            $in = date_create('2013-10-27'); // create a sample date object
            $in->setDate($in->format('Y'), $date, $in->format('d'));
            return $in->format("M");
        }
        if ($period == 'all') {
            $in = date_create('2013-10-27'); // create a sample date object
            $in->setDate($date, $in->format('m'), $in->format('d'));
            return $in->format("Y");
        }
        return $date;
    }

    function formatUserDataDate($date, $period)
    {
        if ($period == 'week') {
            $in = date_create($date);
            return date_format($in, "D");
        }
        if ($period == 'month') {
            $in = date_create($date);
            return date_format($in, "d");
        }
        if ($period == 'year') {
            $in = date_create('2013-10-27'); // create a sample date object
            $in->setDate($in->format('Y'), $date, $in->format('d'));
            return $in->format("M");
        }
        if ($period == 'all') {
            $in = date_create('2013-10-27'); // create a sample date object
            $in->setDate($date, $in->format('m'), $in->format('d'));
            return $in->format("Y");
        }
        return $date;
    }
}
