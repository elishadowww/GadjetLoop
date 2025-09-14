<?php
require_once '../includes/config.php';

// Get period from GET, default to 12 months
$period = isset($_GET['period']) ? intval($_GET['period']) : 12;
if (!in_array($period, [1, 3, 6, 12])) {
    $period = 12;
}

// Get all months in the period
$months = [];
$current = new DateTime();

if ($period == 1) {
    // Last 1 month: last 30 days, show each day as a label
    $startDate = (new DateTime())->modify('-29 days')->format('Y-m-d');
    for ($i = 0; $i < 30; $i++) {
        $day = (new DateTime($startDate))->modify("+$i days");
        $months[] = $day->format('Y-m-d');
    }

    // Query sales data for last 30 days, group by day
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as day,
            COUNT(*) as order_count,
            SUM(total_amount) as revenue
        FROM orders 
        WHERE created_at >= :startDate
        GROUP BY DATE(created_at)
        ORDER BY day
    ");
    $stmt->execute(['startDate' => $startDate]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Index data by day
    $dataByDay = [];
    foreach ($data as $row) {
        $dataByDay[$row['day']] = $row;
    }

    // Fill missing days with zeros
    $result = [];
    foreach ($months as $day) {
        if (isset($dataByDay[$day])) {
            $result[] = [
                'month' => $day,
                'order_count' => $dataByDay[$day]['order_count'],
                'revenue' => $dataByDay[$day]['revenue']
            ];
        } else {
            $result[] = [
                'month' => $day,
                'order_count' => 0,
                'revenue' => 0
            ];
        }
    }
} else {
    // For 3, 6, 12: previous N full months
    for ($i = $period; $i >= 1; $i--) {
        $month = clone $current;
        $month->modify("first day of -$i month");
        $months[] = $month->format('Y-m');
    }
    $startDate = (clone $current)->modify("first day of -$period month")->format('Y-m-d');

    // Query sales data for selected months, group by month
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as order_count,
            SUM(total_amount) as revenue
        FROM orders 
        WHERE created_at >= :startDate
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month
    ");
    $stmt->execute(['startDate' => $startDate]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Index data by month
    $dataByMonth = [];
    foreach ($data as $row) {
        $dataByMonth[$row['month']] = $row;
    }

    // Fill missing months with zeros
    $result = [];
    foreach ($months as $month) {
        if (isset($dataByMonth[$month])) {
            $result[] = [
                'month' => $month,
                'order_count' => $dataByMonth[$month]['order_count'],
                'revenue' => $dataByMonth[$month]['revenue']
            ];
        } else {
            $result[] = [
                'month' => $month,
                'order_count' => 0,
                'revenue' => 0
            ];
        }
    }
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($result);