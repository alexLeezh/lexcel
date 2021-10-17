<?php

/*
 * This file is part of execl setting.
 *
 * (c) zxgbuynow <zxgbuynow@gmail.com>
 * config('ixport.TEACHER_CELL_DATAS')
 */

return [

    //多维表头
    'TEACHER_CELL_DATAS'    => [
        ['序号', '分组', '姓名', '办公电话', '住宅电话', '手机', '编号', '性别', '民族', '出生年月', '政治面貌', '籍贯',
            '行政职务', '任职时间', '职务级别', '专业技术职务', '职称', '评审通过时间', '聘任时间', '系列',
            '所在单位', '所属教研室和实验室', '来校工作时间', '原工作单位', '教师资格证书编号', '身份证号', '老师毕业院校', '第一学历', '', '', '',
            '最高学历学位', '', '', '', '现从事专业', '所属学科', '任教课程', '硕(博)导'],

        ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '学历/学位', '毕业院校', '所学专业',
            '毕业时间', '学历/学位', '毕业院校', '所学专业', '毕业时间', '', '', '',
            '授予单位', '获得时间'],
    ],

    //需导出报表
    'SCHOOL_REPORT'         => [
        'modern'  => '现代化监测指标报告',
        'balance' => '优质均衡发展七项指标报告',
    ],
    'SCHOOL_MODERN_REPORT'  => [
        'kindergarten'=>'幼儿园',
        'primarySchool'=>'小学',
        'juniorMiddleSchool'=>'初中',
        'highSchool'=>'普高',
        'secondaryVocationalSchool'=>'中职',
        'specialSchool'=>'特殊教育',
    ],
    'SCHOOL_MODERN_CONFIG'  => [
        'kindergarten'=>[
           ['幼儿园现代化监测指标报告','','','','','','',''],
           ['序号', '学校', '班师比', '', '', '专科以上老师占比', '' ,''],
           ['', '', '幼儿园专任教师数/班级数', '', '', '幼儿园专科及以上教师数/专任教师总数', '' ,''],
           ['', '', '实际值', '达标值', '是否达标', '实际值', '达标值', '是否达标'],
        ],
        'primarySchool'=>[
            ['小学现代化监测指标报告','','','','','','','','','','','','','','','',''],
            ['序号','学校','生师比','','','本科以上老师占比','','','四十人及一下班级占比','','','每百名学生与中高级以上职称教师比','','生均图书比','','',''],
            ['','','小学学生总数/ 小学专任教师总数','','','小学本科及以上教师数/专任教师总数','','','40人及以下班级数/总班级数','','','（正高级+副高级+中级）/小学生总数','','图书数/学生数','','',''],
            ['','','实际值','达标值','是否达标','实际值','达标值','是否达标','实际值','达标值','是否达标','实际值','达标值','是否达标','实际值','达标值','是否达标']
        ],
        'juniorMiddleSchool'=>[
            ['初中现代化监测指标报告','','','','','','','','','','','','','','','',''],
            ['序号','学校','生师比','','','专科以上老师占比','','','四十五人及一下班级占比','','','每百名学生与中高级以上职称教师比','','','生均图书比','',''],
            ['','','初中学生总数/ 初中专任教师总数','','','初中研究生及以上教师数/专任教师总数','','','45人及以下班级数/总班级数','','','（正高级+副高级+中级）/小学生总数','','','图书数/学生数','',''],
            ['','','实际值','达标值','是否达标','实际值','达标值','是否达标','实际值','达标值','是否达标','实际值','达标值','是否达标','实际值','达标值','是否达标']
        ],
        'highSchool'=>[
            ['普高现代化监测指标报告','','','','','','','','','',''],
            ['序号','学校','生师比','','','专科以上老师占比','','','生均仪器设备比','',''],
            ['','','普高学生总数/ 普高专任教师总数','','','普高研究生及以上教师数/高中教师总数','','','教学仪器设备值/学生数','',''],
            ['','','实际值','达标值','是否达标','实际值','达标值','是否达标','实际值','达标值','是否达标']
        ],
        'secondaryVocationalSchool'=>[
            ['中职现代化监测指标报告','','','','','','','','','',''],
            ['序号','学校','生师比','','','中职学校专业教师双师型比例','','','生均仪器设备比','',''],
            ['','','中职学生数/中职教师数','','','双师型教师数/专业课教师数','','','教学仪器设备值/学生数','',''],
            ['','','实际值','达标值','是否达标','实际值','达标值','是否达标','实际值','达标值','是否达标']
        ],
        'specialSchool'=>[
            ['特殊教育现代化监测指标报告','','','',''],
            ['序号','学校','特殊教育学校生师比','',''],
            ['','','特殊学校学生数/特殊学校教师数','',''],
            ['','','实际值','达标值','是否达标']
        ]
    ],
    'SCHOOL_BALANCE_REPORT' => [
        'primarySchool'=>'小学',
        'juniorMiddleSchool'=>'初中',
        'nineYearCon'=>'九年一贯',
    ],
    'SCHOOL_BALANCE_CONFIG' => [
        'primarySchool'=>[
            ['小学优质均衡发展七项指标报告','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''],
            ['序号','学校','每百生高于规定学历教师数','','','','每百生骨干教师数','','','','每百生体育、艺术专任教师数','','','','生均教学及辅助用房面积','','','','生均体育运动场馆面积','','','','生均教学仪器设备值','','','','每百名学生拥有网络多媒体教室数','','',''],
            ['','','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标']
        ],
        'juniorMiddleSchool'=>[
            ['初中优质均衡发展七项指标报告','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''],
            ['序号','学校','每百生高于规定学历教师数','','','','每百生骨干教师数','','','','每百生体育、艺术专任教师数','','','','生均教学及辅助用房面积','','','','生均体育运动场馆面积','','','','生均教学仪器设备值','','','','每百名学生拥有网络多媒体教室数','','',''],
            ['','','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标']
        ],
        'nineYearCon'=>[
            ['九年一贯优质均衡发展七项指标报告','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''],
            ['序号','学校','每百生高于规定学历教师数','','','','每百生骨干教师数','','','','每百生体育、艺术专任教师数','','','','生均教学及辅助用房面积','','','','生均体育运动场馆面积','','','','生均教学仪器设备值','','','','每百名学生拥有网络多媒体教室数','','',''],
            ['','','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标','基本值','达标值','实际值','是否达标']
        ]
    ],

    //import 
    'SCHOOL_IMPORT_TYPE'  => [
        'kindergarten'=>'幼儿园',
        'primarySchool'=>'小学',
        'juniorMiddleSchool'=>'初中',
        'highSchool'=>'普高',
        'secondaryVocationalSchool'=>'中职',
    ],
    'SCHOOL_IMPORT_TYPE_CONFIG' =>[
        'kindergarten'=>[
            'cover'=>'封面','catalogue'=>'目录','b111'=>'基础基111','b112'=>'基础基112','b112-1'=>'基础基112-1','b213'=>'基础基213','b314'=>'基础基314','b331'=>'基础基331','b332'=>'基础基332','b333'=>'基础基333','b341'=>'基础基341','b412'=>'基础基412','b422'=>'基础基422','b424'=>'基础基424','b431'=>'基础基431','b4411'=>'基础基4411','b4412'=>'基础基4412','b442'=>'基础基442','b512'=>'基础基512','b522'=>'基础基522','b531'=>'基础基531','b532'=>'基础基532'
        ],
    ],
    'SCHOOL_IMPORT_MODERN_FOUND_INDEX'=>[
        'kindergarten'=>['KCTR'=>'班师比','KSTR'=>'专科以上老师占比'],
        'primarySchool'=>['PSTR'=>'生师比','PTR'=>'本科以上老师占比','PFCR'=>'四十人及一下班级占比','PHSTR'=>'每百名学生与中高级以上职称教师比','PSBR'=>'生均图书比'],
        'juniorMiddleSchool'=>['JSTR'=>'生师比','JETR'=>'专科以上老师占比','JFCR'=>'四十五人及一下班级占比','JHSTR'=>'每百名学生与中高级以上职称教师比','JSBR'=>'生均图书比'],
        'highSchool'=>['HSTR'=>'生师比','HETR'=>'专科以上老师占比','HSMR'=>'生均仪器设备比'],
        'secondaryVocationalSchool'=>['VSTR'=>'生师比','VETR'=>'中职学校专业教师双师型比例','VSMR'=>'生均仪器设备比'],
        'specialSchool'=>['SSTR'=>'特殊教育学校生师比'],
    ],
    'SCHOOL_IMPORT_BALANCE_FOUND_INDEX'=>[
        'primarySchool'=>['PHETR'=>'每百生高于规定学历教师数','PHBTR'=>'每百生骨干教师数','PHATR'=>'每百生体育、艺术专任教师数','PSRAR'=>'生均教学及辅助用房面积','PSMAR'=>'生均体育运动场馆面积','PSMR'=>'生均教学仪器设备值','PHIR'=>'每百名学生拥有网络多媒体教室数'],
        'nineYearCon'=>['NHETR'=>'每百生高于规定学历教师数','NHBTR'=>'每百生骨干教师数','NHATR'=>'每百生体育、艺术专任教师数','NSRAR'=>'生均教学及辅助用房面积','NSMAR'=>'生均体育运动场馆面积','NSMR'=>'生均教学仪器设备值','NHIR'=>'每百名学生拥有网络多媒体教室数'],
        'juniorMiddleSchool'=>['JHETR'=>'每百生高于规定学历教师数','JHBTR'=>'每百生骨干教师数','JHATR'=>'每百生体育、艺术专任教师数','JSRAR'=>'生均教学及辅助用房面积','JSMAR'=>'生均体育运动场馆面积','JSMR'=>'生均教学仪器设备值','JHIR'=>'每百名学生拥有网络多媒体教室数']
    ],
    'SCHOOL_IMPORT_FOUND_INDEX'=>[
        'KCTR'=>['found_name'=>'班师比','standard_val'=>'2','basic_val'=>'','ratio'=>'default', 'unit'=>''],
        'KSTR'=>['found_name'=>'专科以上老师占比','standard_val'=>'98%','basic_val'=>'','ratio'=>'percent', 'unit'=>''],
        'PSTR'=>['found_name'=>'生师比','standard_val'=>'17:1','basic_val'=>'','ratio'=>'scale', 'unit'=>''],

        'PTR'=>['found_name'=>'本科以上老师占比','standard_val'=>'90%','basic_val'=>'','ratio'=>'percent', 'unit'=>''],
        'PFCR'=>['found_name'=>'四十人及一下班级占比','standard_val'=>'90%','basic_val'=>'','ratio'=>'percent', 'unit'=>''],
        'PHSTR'=>['found_name'=>'每百名学生与中高级以上职称教师比','standard_val'=>'4','basic_val'=>'','ratio'=>'default', 'unit'=>''],
        'PSBR'=>['found_name'=>'生均图书比','standard_val'=>'30','basic_val'=>'','ratio'=>'default', 'unit'=>'册'],
        'JSTR'=>['found_name'=>'生师比','standard_val'=>'13.5:1','basic_val'=>'','ratio'=>'scale', 'unit'=>''],
        'JETR'=>['found_name'=>'专科以上老师占比','standard_val'=>'90%','basic_val'=>'','ratio'=>'percent', 'unit'=>''],
        'JFCR'=>['found_name'=>'四十五人及一下班级占比','standard_val'=>'90%','basic_val'=>'','ratio'=>'percent', 'unit'=>''],
        'JHSTR'=>['found_name'=>'每百名学生与中高级以上职称教师比','standard_val'=>'6','basic_val'=>'','ratio'=>'default', 'unit'=>''],
        'JSBR'=>['found_name'=>'生均图书比','standard_val'=>'40','basic_val'=>'','ratio'=>'default', 'unit'=>'册'],

        'HSTR'=>['found_name'=>'生师比','standard_val'=>'12.5:1','basic_val'=>'','ratio'=>'scale', 'unit'=>''],
        'HETR'=>['found_name'=>'专科以上老师占比','standard_val'=>'90%','basic_val'=>'','ratio'=>'percent', 'unit'=>''],
        'HSMR'=>['found_name'=>'生均仪器设备比','standard_val'=>'6500','basic_val'=>'','ratio'=>'default', 'unit'=>'元'],
        'VSTR'=>['found_name'=>'生师比','standard_val'=>'12:1','basic_val'=>'','ratio'=>'scale', 'unit'=>''],
        'VETR'=>['found_name'=>'中职学校专业教师双师型比例','standard_val'=>'90%','basic_val'=>'','ratio'=>'percent', 'unit'=>''],
        'VSMR'=>['found_name'=>'生均仪器设备比','standard_val'=>'7500','basic_val'=>'','ratio'=>'default', 'unit'=>'元'],
        'SSTR'=>['found_name'=>'特殊教育学校生师比','standard_val'=>'3:1','basic_val'=>'','ratio'=>'scale', 'unit'=>''],

        'PHETR'=>['found_name'=>'每百生高于规定学历教师数','standard_val'=>'4.2','basic_val'=>'3.57','ratio'=>'default', 'unit'=>'人'],
        'PHBTR'=>['found_name'=>'每百生骨干教师数','standard_val'=>'1','basic_val'=>'0.85','ratio'=>'default', 'unit'=>'人'],
        'PHATR'=>['found_name'=>'每百生体育、艺术专任教师数','standard_val'=>'0.9','basic_val'=>'0.765','ratio'=>'default', 'unit'=>'人'],
        'PSRAR'=>['found_name'=>'生均教学及辅助用房面积','standard_val'=>'4.5','basic_val'=>'3.825','ratio'=>'default', 'unit'=>'平方米'],
        'PSMAR'=>['found_name'=>'生均体育运动场馆面积','standard_val'=>'7.5','basic_val'=>'6.375','ratio'=>'default', 'unit'=>'平方米'],
        'PSMR'=>['found_name'=>'生均教学仪器设备值','standard_val'=>'2000','basic_val'=>'1700','ratio'=>'default', 'unit'=>'元'],
        'PHIR'=>['found_name'=>'每百名学生拥有网络多媒体教室数','standard_val'=>'2.3','basic_val'=>'1.955','ratio'=>'default', 'unit'=>'间'],

        'JHETR'=>['found_name'=>'每百生高于规定学历教师数','standard_val'=>'5.3','basic_val'=>'4.505','ratio'=>'default', 'unit'=>'人'],
        'JHBTR'=>['found_name'=>'每百生骨干教师数','standard_val'=>'1','basic_val'=>'0.85','ratio'=>'default', 'unit'=>'人'],
        'JHATR'=>['found_name'=>'每百生体育、艺术专任教师数','standard_val'=>'0.9','basic_val'=>'0.765','ratio'=>'default', 'unit'=>'人'],
        'JSRAR'=>['found_name'=>'生均教学及辅助用房面积','standard_val'=>'5.8','basic_val'=>'4.93','ratio'=>'default', 'unit'=>'平方米'],
        'JSMAR'=>['found_name'=>'生均体育运动场馆面积','standard_val'=>'10.2','basic_val'=>'8.67','ratio'=>'default', 'unit'=>'平方米'],
        'JSMR'=>['found_name'=>'生均教学仪器设备值','standard_val'=>'2500','basic_val'=>'2125','ratio'=>'default', 'unit'=>'元'],
        'JHIR'=>['found_name'=>'每百名学生拥有网络多媒体教室数','standard_val'=>'2.4','basic_val'=>'2.04','ratio'=>'default', 'unit'=>'间'],

        'NHETR'=>['found_name'=>'每百生高于规定学历教师数','standard_val'=>'4.2','basic_val'=>'3.57','ratio'=>'default', 'unit'=>'人'],
        'NHBTR'=>['found_name'=>'每百生骨干教师数','standard_val'=>'1','basic_val'=>'0.85','ratio'=>'default', 'unit'=>'人'],
        'NHATR'=>['found_name'=>'每百生体育、艺术专任教师数','standard_val'=>'0.9','basic_val'=>'0.765','ratio'=>'default', 'unit'=>'人'],
        'NSRAR'=>['found_name'=>'生均教学及辅助用房面积','standard_val'=>'4.5','basic_val'=>'3.825','ratio'=>'default', 'unit'=>'平方米'],
        'NSMAR'=>['found_name'=>'生均体育运动场馆面积','standard_val'=>'7.5','basic_val'=>'6.375','ratio'=>'default', 'unit'=>'平方米'],
        'NSMR'=>['found_name'=>'生均教学仪器设备值','standard_val'=>'2000','basic_val'=>'1700','ratio'=>'default', 'unit'=>'元'],
        'NHIR'=>['found_name'=>'每百名学生拥有网络多媒体教室数','standard_val'=>'2.3','basic_val'=>'1.955','ratio'=>'default', 'unit'=>'间'],

        'NJHETR'=>['found_name'=>'每百生高于规定学历教师数','standard_val'=>'5.3','basic_val'=>'4.505','ratio'=>'default', 'unit'=>'人'],
        'NJHBTR'=>['found_name'=>'每百生骨干教师数','standard_val'=>'1','basic_val'=>'0.85','ratio'=>'default', 'unit'=>'人'],
        'NJHATR'=>['found_name'=>'每百生体育、艺术专任教师数','standard_val'=>'0.9','basic_val'=>'0.765','ratio'=>'default', 'unit'=>'人'],
        'NJSRAR'=>['found_name'=>'生均教学及辅助用房面积','standard_val'=>'5.8','basic_val'=>'4.93','ratio'=>'default', 'unit'=>'平方米'],
        'NJSMAR'=>['found_name'=>'生均体育运动场馆面积','standard_val'=>'10.2','basic_val'=>'8.67','ratio'=>'default', 'unit'=>'平方米'],
        'NJSMR'=>['found_name'=>'生均教学仪器设备值','standard_val'=>'2500','basic_val'=>'2125','ratio'=>'default', 'unit'=>'元'],
        'NJHIR'=>['found_name'=>'每百名学生拥有网络多媒体教室数','standard_val'=>'2.4','basic_val'=>'2.04','ratio'=>'default', 'unit'=>'间'],
    ],
];
