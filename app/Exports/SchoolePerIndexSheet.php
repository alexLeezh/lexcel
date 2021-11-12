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
use Illuminate\Support\Facades\Log;
use App\Models\User;

//ShouldAutoSize
class SchoolePerIndexSheet implements FromCollection, WithTitle , WithHeadings, WithEvents, ShouldAutoSize, WithColumnWidths
{
    private $tbNm;
    private $type;
    private $heads = [];
    private $school_type;
    private $qureyData = [];
    public function __construct(string $tbNm, string $type, array $heads, string $school_type, $qureyData)
    {
        $this->tbNm  = $tbNm;
        $this->type = $type;
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
            'B' => 30,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 20,
            'I' => 20,
            'J' => 20,
            'K' => 20,
            'L' => 20,
            'M' => 20,
            'N' => 20,
            'O' => 20,
            'P' => 20,
            'Q' => 20,
            'R' => 20,
            'S' => 20,
            'T' => 20,
            'U' => 20,
            'V' => 20,
            'W' => 20,
            'X' => 20,
            'Y' => 20,
            'Z' => 20,
            'AA' => 20,
            'AB' => 20,
            'AC' => 20,
            'AD' => 20,
            'AE' => 20,
            'AF' => 20,
            'AG' => 20,
            'AH' => 20,
            'AI' => 20,
            'AJ' => 20,
            'AK' => 20,
            'AL' => 20,
            'AM' => 20,
            'AN' => 20,
            'AO' => 20,
            'AP' => 20,
            'AQ' => 20,
            'AR' => 20,
            'AS' => 20,
            'AT' => 20,
            'AU' => 20,
            'AV' => 20,
            'AW' => 20,
            'AX' => 20,
            'AY' => 20,
            'AZ' => 20,

            'BA' => 20,
            'BB' => 20,
            'BC' => 20,
            'BD' => 20,
            'BE' => 20,
            'BF' => 20,
            'BG' => 20,
            'BH' => 20,
            'BI' => 20,
            'BJ' => 20,
            'BK' => 20,
            'BL' => 20,
            'BM' => 20,
            'BN' => 20,
            'BO' => 20,

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
                        $cells = ['A1:L1','A2:A4','B2:B4','C2:G2','C3:G3','H2:L2','H3:L3'];

                        $event->sheet->getDelegate()->getStyle('A1:L1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:L4')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                        // $styleArray = [
                        //     'allborders' => [
                        //         'outline' => [
                        //             'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        //             'color' => ['argb' => '#0C0C0C'],
                        //         ],
                        //     ],
                        // ];

                        // $event->sheet->getStyle('A1:L4')->applyFromArray($styleArray);
                        $event->sheet->getDelegate()->getDefaultColumnDimension()->setWidth(50);

                        foreach ($cells as $k=>$v) {
                            $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getDelegate()->mergeCells($v);
                        }

                        $event->sheet->getDelegate()->getStyle('C4:L4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }
                ];
                break;
            case 'primarySchool':
                Log::info('SchoolePerIndexSheet'.$this->type);
                if ($this->type == 'modern') {
                    Log::info('SchoolePerIndexSheet modern');
                    return [
                        AfterSheet::class  => function(AfterSheet $event) {
                            $cells = ['A1:AA1','A2:A4','B2:B4','C2:G2','H2:L2','C3:G3','H3:L3','M2:Q2','R2:V2','W2:AA2','M3:Q3','R3:V3','W3:AA3'];

                            $event->sheet->getDelegate()->getStyle('A1:AA1')->getFont()->setSize(15);
                            $event->sheet->getDelegate()->getStyle('A2:AA4')->getFont()->setSize(12);
                            $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                            foreach ($cells as $k=>$v) {
                                $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                $event->sheet->getDelegate()->mergeCells($v);
                            }
                        }
                    ];
                    break;
                }
                 Log::info('SchoolePerIndexSheet  outline');
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                        $cells = ['A1:AR1','A2:A3','B2:B3','C2:H2','I2:N2','O2:T2','U2:Z2','AA2:AF2','AG2:AL2','AM2:AR2'];
                        $event->sheet->getDelegate()->getStyle('A1:AR1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:AR3')->getFont()->setSize(12);
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
                            $cells = ['A1:AA1','A2:A4','B2:B4','C2:G2','H2:L2','C3:G3','H3:L3','M2:Q2','R2:V2','W2:AA2','M3:Q3','R3:V3','W3:AA3'];

                            $event->sheet->getDelegate()->getStyle('A1:AA1')->getFont()->setSize(15);
                            $event->sheet->getDelegate()->getStyle('A2:AA4')->getFont()->setSize(12);
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
                        $cells = ['A1:AR1','A2:A3','B2:B3','C2:H2','I2:N2','O2:T2','U2:Z2','AA2:AF2','AG2:AL2','AM2:AR2'];
                        $event->sheet->getDelegate()->getStyle('A1:AR1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:AR3')->getFont()->setSize(12);
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
                        $cells = ['A1:Q1','A2:A4','B2:B4','C2:G2','C3:G3','H2:L2','H3:L3','M2:Q2','M3:Q3'];

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
            case 'secondaryVocationalSchool':
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                         $cells = ['A1:Q1','A2:A4','B2:B4','C2:G2','C3:G3','H2:L2','H3:L3','M2:Q2','M3:Q3'];

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
            case 'specialSchool':
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                        $cells = ['A1:G1','A2:A4','B2:B4','C2:G2','C3:G3'];

                        $event->sheet->getDelegate()->getStyle('A1:G1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:G4')->getFont()->setSize(12);
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
                        $cells = ['A1:AR1','A2:A3','B2:B3','C2:H2','I2:N2','O2:T2','U2:Z2','AA2:AF2','AG2:AL2','AM2:AR2'];

                        $event->sheet->getDelegate()->getStyle('A1:AR1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:AR3')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                        foreach ($cells as $k=>$v) {
                            $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getDelegate()->mergeCells($v);
                        }
                    }
                ];
                break;

            case 'twelveYearCon':
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                        $cells = ['A1:AR1','A2:A3','B2:B3','C2:H2','I2:N2','O2:T2','U2:Z2','AA2:AF2','AG2:AL2','AM2:AR2'];

                        $event->sheet->getDelegate()->getStyle('A1:AR1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:AR3')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                        foreach ($cells as $k=>$v) {
                            $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getDelegate()->mergeCells($v);
                        }
                    }
                ];
                break;
            case 'mnineYearCon':
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                        $cells = ['A1:AZ1','A2:A4','B2:B4','C2:G2','C3:G3','H2:L2','H3:L3','M2:Q2','M3:Q3','R2:V2','R3:V3','W2:AA2','W3:AA3','AB2:AF2','AB3:AF3','AG2:AK2','AG3:AK3','AL2:AP2','AL3:AP3','AQ2:AU2','AQ3:AU3','AV2:AZ2','AV3:AZ3'];

                        $event->sheet->getDelegate()->getStyle('A1:AZ1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:AZ4')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                        foreach ($cells as $k=>$v) {
                            $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getDelegate()->mergeCells($v);
                        }
                    }
                ];
                break;
            case 'mtwelveYearCon':
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                        $cells = ['A1:BO1','A2:A4','B2:B4','C2:G2','C3:G3','H2:L2','H3:L3','M2:Q2','M3:Q3','R2:V2','R3:V3','W2:AA2','W3:AA3','AB2:AF2','AB3:AF3','AG2:AK2','AG3:AK3','AL2:AP2','AL3:AP3','AQ2:AU2','AQ3:AU3','AV2:AZ2','AV3:AZ3','BA2:BE2','BA3:BE3','BF2:BJ2','BF3:BJ3','BK2:BO2','BK3:BO3'];

                        $event->sheet->getDelegate()->getStyle('A1:BO1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:BO4')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);

                        foreach ($cells as $k=>$v) {
                            $event->sheet->getDelegate()->getStyle($v)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $event->sheet->getDelegate()->mergeCells($v);
                        }
                    }
                ];
                break;
            case 'summary':
                return [
                    AfterSheet::class  => function(AfterSheet $event) {
                        $cells = ['A1:J1','A2:B3','A4:A7'];
                        Log::info('SchoolePerIndexSheet summary');
                        Log::info($event->sheet->getDelegate()->getRowDimensions());
                        $event->sheet->getDelegate()->getStyle('A1:J1')->getFont()->setSize(15);
                        $event->sheet->getDelegate()->getStyle('A2:J3')->getFont()->setSize(12);
                        $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);
                        $event->sheet->getDelegate()->getStyle('A2:J2')->getAlignment()->setWrapText(true);
                        $event->sheet->getDelegate()->getStyle('A2:J7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        $event->sheet->getDelegate()->getStyle('A4:A7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                        $styleArray = [
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '#7E7676'],
                                ],
                            ],
                        ];

                        $event->sheet->getStyle('A1:J7')->applyFromArray($styleArray);
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
