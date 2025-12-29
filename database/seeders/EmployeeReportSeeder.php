<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\AnalysisOverview;
use App\Models\AnalysisResult;
use App\Enums\AnalysisType;
use App\Enums\AnalysisStatus;
use Illuminate\Database\Seeder;

class EmployeeReportSeeder extends Seeder
{
    /**
     * Seed employee report data for branch ID 6
     */
    public function run(): void
    {
        $branch = Branch::find(6);

        if (!$branch) {
            $this->command->error('Branch ID 6 not found!');
            return;
        }

        // Find or create analysis overview for this branch
        $analysisOverview = AnalysisOverview::where('branch_id', $branch->id)
            ->where('status', AnalysisStatus::COMPLETED)
            ->latest()
            ->first();

        if (!$analysisOverview) {
            $analysisOverview = AnalysisOverview::create([
                'tenant_id' => $branch->tenant_id,
                'branch_id' => $branch->id,
                'restaurant_id' => $branch->restaurant_id,
                'status' => AnalysisStatus::COMPLETED,
                'progress' => 100,
                'total_reviews' => 150,
                'period_start' => now()->subMonths(3),
                'period_end' => now(),
            ]);
        }

        // Employee insights data
        $employeeData = [
            'overview' => [
                'mostPositiveEmployee' => [
                    'name' => 'أحمد محمد',
                    'totalMentions' => 45,
                    'averageRating' => 4.8,
                    'topPositives' => [
                        'خدمة ممتازة وابتسامة دائمة',
                        'سرعة في التقديم',
                        'اهتمام بالتفاصيل',
                        'تعامل راقي مع العملاء'
                    ],
                    'topNegatives' => [],
                    'improvementPoints' => [
                        'زيادة المعرفة بقائمة الطعام'
                    ],
                    'positiveKeywords' => ['ممتاز', 'سريع', 'مبتسم', 'محترف']
                ],
                'mostMentionedEmployee' => [
                    'name' => 'محمد علي',
                    'totalMentions' => 62,
                    'averageRating' => 4.2,
                    'topPositives' => [
                        'متواجد دائماً للمساعدة',
                        'يتذكر طلبات العملاء المعتادين',
                        'لبق في التعامل'
                    ],
                    'topNegatives' => [
                        'أحياناً يتأخر في الرد'
                    ],
                    'improvementPoints' => [
                        'تحسين سرعة الاستجابة',
                        'الاهتمام بالعملاء الجدد'
                    ],
                    'positiveKeywords' => ['متعاون', 'ودود', 'يتذكرني']
                ],
                'mostNegativeEmployee' => [
                    'name' => 'خالد سعيد',
                    'totalMentions' => 28,
                    'averageRating' => 2.4,
                    'topPositives' => [
                        'يعرف القائمة جيداً'
                    ],
                    'topNegatives' => [
                        'تعامل جاف مع العملاء',
                        'لا يبتسم أبداً',
                        'بطيء في الخدمة',
                        'يبدو غير مهتم'
                    ],
                    'improvementPoints' => [
                        'تحسين مهارات التواصل',
                        'الابتسامة والترحيب',
                        'سرعة الاستجابة للطلبات',
                        'الاهتمام براحة العميل'
                    ],
                    'negativeKeywords' => ['بطيء', 'جاف', 'غير مهتم']
                ]
            ],
            'performance' => [
                [
                    'name' => 'أحمد محمد',
                    'totalMentions' => 45,
                    'averageRating' => 4.8,
                    'performanceNote' => 'up',
                    'positiveCount' => 42,
                    'negativeCount' => 3
                ],
                [
                    'name' => 'سارة أحمد',
                    'totalMentions' => 38,
                    'averageRating' => 4.5,
                    'performanceNote' => 'stable',
                    'positiveCount' => 35,
                    'negativeCount' => 3
                ],
                [
                    'name' => 'محمد علي',
                    'totalMentions' => 62,
                    'averageRating' => 4.2,
                    'performanceNote' => 'stable',
                    'positiveCount' => 50,
                    'negativeCount' => 12
                ],
                [
                    'name' => 'فاطمة حسن',
                    'totalMentions' => 25,
                    'averageRating' => 4.0,
                    'performanceNote' => 'up',
                    'positiveCount' => 20,
                    'negativeCount' => 5
                ],
                [
                    'name' => 'عمر يوسف',
                    'totalMentions' => 30,
                    'averageRating' => 3.5,
                    'performanceNote' => 'down',
                    'positiveCount' => 18,
                    'negativeCount' => 12
                ],
                [
                    'name' => 'خالد سعيد',
                    'totalMentions' => 28,
                    'averageRating' => 2.4,
                    'performanceNote' => 'down',
                    'positiveCount' => 8,
                    'negativeCount' => 20
                ]
            ]
        ];

        // Delete existing employee insights for this analysis
        AnalysisResult::where('analysis_overview_id', $analysisOverview->id)
            ->where('analysis_type', AnalysisType::EMPLOYEES_INSIGHTS)
            ->delete();

        // Create the analysis result
        AnalysisResult::create([
            'analysis_overview_id' => $analysisOverview->id,
            'restaurant_id' => $branch->restaurant_id,
            'analysis_type' => AnalysisType::EMPLOYEES_INSIGHTS,
            'result' => $employeeData,
            'status' => AnalysisStatus::COMPLETED,
            'provider' => 'seeder',
            'model' => 'manual',
            'processing_time' => 0,
            'tokens_used' => 0,
            'confidence' => 1.0,
            'review_count' => 150,
            'period_start' => now()->subMonths(3),
            'period_end' => now(),
        ]);

        $this->command->info('Employee report data seeded successfully for branch ID 6!');
    }
}
