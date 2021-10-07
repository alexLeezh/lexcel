<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\User;

class SchoolePerIndexSheet implements FromCollection, WithTitle , WithHeadings, WithEvents, ShouldAutoSize
{
    private $tbNm;
    private $type;
    private $heads = [];
    private $school_type;
    private $qureyData = [];
    public function __construct(string $tbNm, string $type, array $heads, string $school_type, $qureyData)
    {
        $this->tbNm  = $tbNm;
        $this->$type = $type;
        $this->heads = array_values($heads);
        $this->school_type = $school_type;
        $this->qureyData = $qureyData;
        // var_dump($qureyData);exit;
    }

    public function headings(): array
    {
        return $this->heads;
    }

    public function collection()
    {
        return $this->qureyData;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'C' => 10,
            'D' => 35,
            'H' => 10,
            'I' => 10,
            'J' => 25,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->tbNm;
    }

    public function registerEvents(): array
    {
        switch ($this->school_type) {
            case 'kindergarten':
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                        $cells = ['A1:H1','A2:A4','B2:B4','C2:E2','F2:H2','C3:E3','F3:H3'];

                        $event->sheet->getDelegate()->getStyle('A1:H1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:H4')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);
                        foreach ($cells as $k=>$v) {
                            $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getDelegate()->mergeCells($v);
                        }
                        $widths = ['A' , 'B', 'C' ,'D' ,'E' ,'F' ,'G' ,'H' ,'J' ,'K' ,'L' ,'M' ,'N' ];
                        foreach ($widths as  $v) {
                            // 设置列宽度
                            $event->sheet->getDelegate()->getColumnDimension($v)->setAutoSize(true);
                        }
                    }
                ];
                break;
            case 'primarySchool':
                if ($this->type == 'modern') {
                    return [
                        AfterSheet::class  => function(AfterSheet $event) {
                            $cells = ['A1:Q1','A2:A4','B2:B4','C2:E2','F2:H2','C3:E3','F3:H3','I2:K2','L2:N2','O2:Q2','I3:K3','L3:N3','O3:Q3'];

                            $event->sheet->getDelegate()->getStyle('A1:Q1')->getFont()->setSize(15);
                            $event->sheet->getDelegate()->getStyle('A2:Q4')->getFont()->setSize(12);
                            $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                            foreach ($cells as $k=>$v) {
                                $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                $event->sheet->getDelegate()->mergeCells($v);
                            }
                        }
                    ];
                    break;
                }
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                        $cells = ['A1:Z1','A2:A4','B2:B4','C2:F2','G2:J2','K2:N2','O2:R2','S2:V2','W2:Z2'];

                        $event->sheet->getDelegate()->getStyle('A1:Z1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:Z3')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                        foreach ($cells as $k=>$v) {
                            $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getDelegate()->mergeCells($v);
                        }
                    }
                ];
                break;
            case 'juniorMiddleSchool':
                if ($this->type == 'modern') {
                    return [
                        AfterSheet::class  => function(AfterSheet $event) {
                            $cells = ['A1:Q1','A2:A4','B2:B4','C2:E2','F2:H2','C3:E3','F3:H3','I2:K2','L2:N2','O2:Q2','I3:K3','L3:N3','O3:Q3'];

                            $event->sheet->getDelegate()->getStyle('A1:Q1')->getFont()->setSize(15);
                            $event->sheet->getDelegate()->getStyle('A2:Q4')->getFont()->setSize(12);
                            $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                            foreach ($cells as $k=>$v) {
                                $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                $event->sheet->getDelegate()->mergeCells($v);
                            }
                        }
                    ];
                    break;
                }
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                        $cells = ['A1:Z1','A2:A4','B2:B4','C2:F2','G2:J2','K2:N2','O2:R2','S2:V2','W2:Z2'];

                        $event->sheet->getDelegate()->getStyle('A1:Z1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:Z3')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                        foreach ($cells as $k=>$v) {
                            $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getDelegate()->mergeCells($v);
                        }
                    }
                ];
                break;
            case 'highSchool':
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                        $cells = ['A1:K1','A2:A4','B2:B4','C2:E2','F2:H2','I2:K2','C3:E3','F3:H3','I3:K3'];

                        $event->sheet->getDelegate()->getStyle('A1:K1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:K4')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                        foreach ($cells as $k=>$v) {
                            $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getDelegate()->mergeCells($v);
                        }
                    }
                ];
                break;
            case 'secondaryVocationalSchool':
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                         $cells = ['A1:K1','A2:A4','B2:B4','C2:E2','F2:H2','I2:K2','C3:E3','F3:H3','I3:K3'];

                        $event->sheet->getDelegate()->getStyle('A1:K1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:K4')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                        foreach ($cells as $k=>$v) {
                            $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getDelegate()->mergeCells($v);
                        }
                    }
                ];
                break;
            case 'specialSchool':
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                        $cells = ['A1:E1','A2:A4','B2:B4','C2:E2','C3:E3'];

                        $event->sheet->getDelegate()->getStyle('A1:E1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:E4')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                        foreach ($cells as $k=>$v) {
                            $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getDelegate()->mergeCells($v);
                        }
                    }
                ];
                break;
            case 'nineYearCon':
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                        $cells = ['A1:Z1','A2:A4','B2:B4','C2:F2','G2:J2','K2:N2','O2:R2','S2:V2','W2:Z2'];

                        $event->sheet->getDelegate()->getStyle('A1:Z1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:Z3')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                        foreach ($cells as $k=>$v) {
                            $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getDelegate()->mergeCells($v);
                        }
                    }
                ];
                break;
            
            default:
                return [];
                break;
        }
        
    }
    

}
