<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AiService
{
    private string $model;
    private string $url;

    private const ASK_CONTRACT_TYPE = 'ASK_CONTRACT_TYPE';
    private const ASK_STATEMENT_TYPE = 'ASK_STATEMENT_TYPE';
    private const ASK_LEAVE_TYPE = 'ASK_LEAVE_TYPE';
    private const ASK_CERTIFICATE_TYPE = 'ASK_CERTIFICATE_TYPE';

    private const DOCUMENTS = [
        'ПРИЁМ_НА_РАБОТУ' => [
            'ru' => 'Заявление о приёме на работу',
            'tj' => 'Ариза барои қабул ба кор',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО заявителя', 'tj' => 'Ном ва насаби аризадиҳанда'],
                ['key' => 'position', 'ru' => 'На какую должность принимается сотрудник?', 'tj' => 'Ба кадом вазифа корманд қабул мешавад?'],
                ['key' => 'organization', 'ru' => 'Название организации', 'tj' => 'Номи ташкилот'],
                ['key' => 'start_date', 'ru' => 'Дата начала работы', 'tj' => 'Санаи оғози кор'],
            ],
        ],
        'УВОЛЬНЕНИЕ' => [
            'ru' => 'Заявление об увольнении',
            'tj' => 'Ариза дар бораи аз кор рафтан',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО заявителя', 'tj' => 'Ном ва насаби аризадиҳанда'],
                ['key' => 'position', 'ru' => 'Должность сотрудника', 'tj' => 'Вазифаи корманд'],
                ['key' => 'organization', 'ru' => 'Название организации', 'tj' => 'Номи ташкилот'],
                ['key' => 'reason', 'ru' => 'Причина увольнения', 'tj' => 'Сабаби аз кор рафтан'],
                ['key' => 'last_work_date', 'ru' => 'Последний рабочий день', 'tj' => 'Рӯзи охирини корӣ'],
            ],
        ],
        'ПЕРЕВОД' => [
            'ru' => 'Заявление о переводе',
            'tj' => 'Ариза барои гузариш ба вазифаи дигар',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'current_position', 'ru' => 'Текущая должность', 'tj' => 'Вазифаи ҳозира'],
                ['key' => 'new_position', 'ru' => 'Новая должность', 'tj' => 'Вазифаи нав'],
                ['key' => 'organization', 'ru' => 'Название организации', 'tj' => 'Номи ташкилот'],
                ['key' => 'reason', 'ru' => 'Причина перевода', 'tj' => 'Сабаби гузариш'],
            ],
        ],
        'ОТПУСК_ОЧЕРЕДНОЙ' => [
            'ru' => 'Заявление на ежегодный отпуск',
            'tj' => 'Ариза барои рухсатии меҳнатии навбатӣ',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'position', 'ru' => 'Должность сотрудника', 'tj' => 'Вазифаи корманд'],
                ['key' => 'organization', 'ru' => 'Название организации', 'tj' => 'Номи ташкилот'],
                ['key' => 'start_date', 'ru' => 'Дата начала отпуска', 'tj' => 'Санаи оғози рухсатӣ'],
                ['key' => 'days', 'ru' => 'Количество дней отпуска', 'tj' => 'Шумораи рӯзҳои рухсатӣ'],
            ],
        ],
        'ОТПУСК_БЕЗ_СОДЕРЖАНИЯ' => [
            'ru' => 'Заявление на отпуск без сохранения заработной платы',
            'tj' => 'Ариза барои рухсатии бемузд',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'position', 'ru' => 'Должность сотрудника', 'tj' => 'Вазифаи корманд'],
                ['key' => 'start_date', 'ru' => 'Дата начала отпуска', 'tj' => 'Санаи оғози рухсатӣ'],
                ['key' => 'days', 'ru' => 'Количество дней', 'tj' => 'Шумораи рӯзҳо'],
                ['key' => 'reason', 'ru' => 'Причина', 'tj' => 'Сабаб'],
            ],
        ],
        'ОТПУСК_ПО_УХОДУ_ЗА_РЕБЁНКОМ' => [
            'ru' => 'Заявление на отпуск по уходу за ребёнком',
            'tj' => 'Ариза барои рухсатӣ вобаста ба нигоҳубини кӯдак',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'child_name', 'ru' => 'ФИО ребёнка', 'tj' => 'Ном ва насаби кӯдак'],
                ['key' => 'child_birth_date', 'ru' => 'Дата рождения ребёнка', 'tj' => 'Санаи таваллуди кӯдак'],
                ['key' => 'start_date', 'ru' => 'Дата начала отпуска', 'tj' => 'Санаи оғози рухсатӣ'],
            ],
        ],
        'ПРЕМИЯ' => [
            'ru' => 'Служебная записка о премировании',
            'tj' => 'Хати хизматӣ дар бораи мукофотонидан',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'position', 'ru' => 'Должность', 'tj' => 'Вазифа'],
                ['key' => 'amount', 'ru' => 'Размер премии', 'tj' => 'Маблағи мукофот'],
                ['key' => 'reason', 'ru' => 'Основание для премирования', 'tj' => 'Асоси мукофотонидан'],
            ],
        ],
        'ВЫГОВОР' => [
            'ru' => 'Приказ о дисциплинарном взыскании',
            'tj' => 'Фармон дар бораи ҷазои интизомӣ',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'position', 'ru' => 'Должность', 'tj' => 'Вазифа'],
                ['key' => 'violation', 'ru' => 'Описание нарушения', 'tj' => 'Тавсифи қонуншиканӣ'],
                ['key' => 'violation_date', 'ru' => 'Дата нарушения', 'tj' => 'Санаи қонуншиканӣ'],
                ['key' => 'reason', 'ru' => 'Основание', 'tj' => 'Асос'],
            ],
        ],
        'ПОВЫШЕНИЕ_ЗАРПЛАТЫ' => [
            'ru' => 'Служебная записка о повышении заработной платы',
            'tj' => 'Хати хизматӣ дар бораи зиёд кардани маош',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'position', 'ru' => 'Должность', 'tj' => 'Вазифа'],
                ['key' => 'current_salary', 'ru' => 'Текущая заработная плата', 'tj' => 'Маоши ҳозира'],
                ['key' => 'new_salary', 'ru' => 'Новая заработная плата', 'tj' => 'Маоши нав'],
                ['key' => 'reason', 'ru' => 'Причина повышения', 'tj' => 'Сабаби зиёд кардани маош'],
            ],
        ],
        'ИЗМЕНЕНИЕ_ДОЛЖНОСТИ' => [
            'ru' => 'Заявление об изменении должности',
            'tj' => 'Ариза барои тағйири вазифа',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'current_position', 'ru' => 'Текущая должность', 'tj' => 'Вазифаи ҳозира'],
                ['key' => 'new_position', 'ru' => 'Новая должность', 'tj' => 'Вазифаи нав'],
                ['key' => 'reason', 'ru' => 'Причина изменения', 'tj' => 'Сабаби тағйирот'],
            ],
        ],
        'КОМАНДИРОВКА' => [
            'ru' => 'Служебная записка о командировке',
            'tj' => 'Хати хизматӣ дар бораи сафари хизматӣ',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'position', 'ru' => 'Должность', 'tj' => 'Вазифа'],
                ['key' => 'destination', 'ru' => 'Место командировки', 'tj' => 'Ҷойи сафари хизматӣ'],
                ['key' => 'start_date', 'ru' => 'Дата начала', 'tj' => 'Санаи оғоз'],
                ['key' => 'end_date', 'ru' => 'Дата окончания', 'tj' => 'Санаи анҷом'],
                ['key' => 'purpose', 'ru' => 'Цель командировки', 'tj' => 'Мақсади сафари хизматӣ'],
            ],
        ],
        'ХАРАКТЕРИСТИКА' => [
            'ru' => 'Характеристика',
            'tj' => 'Тавсифнома',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'position', 'ru' => 'Должность', 'tj' => 'Вазифа'],
                ['key' => 'organization', 'ru' => 'Название организации', 'tj' => 'Номи ташкилот'],
                ['key' => 'work_period', 'ru' => 'Период работы', 'tj' => 'Давраи кор'],
                ['key' => 'characteristics', 'ru' => 'Основные характеристики сотрудника', 'tj' => 'Хусусиятҳои асосии корманд'],
            ],
        ],
        'ДОВЕРЕННОСТЬ' => [
            'ru' => 'Доверенность',
            'tj' => 'Ваколатнома',
            'fields' => [
                ['key' => 'principal_name', 'ru' => 'ФИО доверителя', 'tj' => 'Ном ва насаби ваколатдиҳанда'],
                ['key' => 'representative_name', 'ru' => 'ФИО представителя', 'tj' => 'Ном ва насаби намоянда'],
                ['key' => 'authority', 'ru' => 'Какие полномочия предоставляются?', 'tj' => 'Кадом ваколатҳо дода мешаванд?'],
                ['key' => 'valid_until', 'ru' => 'Срок действия', 'tj' => 'Муҳлати амал'],
            ],
        ],
        'ДОГОВОР_УСЛУГ' => [
            'ru' => 'Договор оказания услуг',
            'tj' => 'Шартномаи хизматрасонӣ',
            'fields' => [
                ['key' => 'customer', 'ru' => 'Наименование или ФИО заказчика', 'tj' => 'Ном ё номи фармоишгар'],
                ['key' => 'provider', 'ru' => 'Наименование или ФИО исполнителя', 'tj' => 'Ном ё номи иҷрокунанда'],
                ['key' => 'service', 'ru' => 'Какая услуга оказывается?', 'tj' => 'Кадом хизматрасонӣ пешниҳод мешавад?'],
                ['key' => 'price', 'ru' => 'Стоимость услуги', 'tj' => 'Арзиши хизматрасонӣ'],
                ['key' => 'term', 'ru' => 'Срок оказания услуги', 'tj' => 'Муҳлати хизматрасонӣ'],
            ],
        ],
        'ДОГОВОР_КУПЛИ_ПРОДАЖИ' => [
            'ru' => 'Договор купли-продажи',
            'tj' => 'Шартномаи хариду фурӯш',
            'fields' => [
                ['key' => 'seller', 'ru' => 'Продавец', 'tj' => 'Фурӯшанда'],
                ['key' => 'buyer', 'ru' => 'Покупатель', 'tj' => 'Харидор'],
                ['key' => 'object', 'ru' => 'Что продаётся?', 'tj' => 'Чӣ фурӯхта мешавад?'],
                ['key' => 'price', 'ru' => 'Цена', 'tj' => 'Нарх'],
                ['key' => 'payment', 'ru' => 'Условия оплаты', 'tj' => 'Шартҳои пардохт'],
            ],
        ],
        'ДОГОВОР_АРЕНДЫ' => [
            'ru' => 'Договор аренды',
            'tj' => 'Шартномаи иҷора',
            'fields' => [
                ['key' => 'landlord', 'ru' => 'Арендодатель', 'tj' => 'Иҷорадиҳанда'],
                ['key' => 'tenant', 'ru' => 'Арендатор', 'tj' => 'Иҷорагир'],
                ['key' => 'object', 'ru' => 'Объект аренды', 'tj' => 'Объекти иҷора'],
                ['key' => 'price', 'ru' => 'Стоимость аренды', 'tj' => 'Арзиши иҷора'],
                ['key' => 'term', 'ru' => 'Срок аренды', 'tj' => 'Муҳлати иҷора'],
            ],
        ],
        'ДОГОВОР_ЗАЙМА' => [
            'ru' => 'Договор займа',
            'tj' => 'Шартномаи қарз',
            'fields' => [
                ['key' => 'lender', 'ru' => 'ФИО или название займодавца', 'tj' => 'Ном ё номи қарздиҳанда'],
                ['key' => 'borrower', 'ru' => 'ФИО или название заёмщика', 'tj' => 'Ном ё номи қарзгир'],
                ['key' => 'amount', 'ru' => 'Сумма займа', 'tj' => 'Маблағи қарз'],
                ['key' => 'return_date', 'ru' => 'Дата возврата', 'tj' => 'Санаи баргардонидан'],
            ],
        ],
        'РАСПИСКА' => [
            'ru' => 'Расписка',
            'tj' => 'Забонхат',
            'fields' => [
                ['key' => 'receiver', 'ru' => 'ФИО получателя', 'tj' => 'Ном ва насаби гиранда'],
                ['key' => 'payer', 'ru' => 'ФИО передавшего', 'tj' => 'Ном ва насаби супоранда'],
                ['key' => 'amount', 'ru' => 'Сумма', 'tj' => 'Маблағ'],
                ['key' => 'purpose', 'ru' => 'Основание передачи', 'tj' => 'Мақсади супоридани маблағ'],
                ['key' => 'return_date', 'ru' => 'Дата возврата, если применимо', 'tj' => 'Санаи баргардонидан, агар лозим бошад'],
            ],
        ],
        'СПРАВКА_С_МЕСТА_РАБОТЫ' => [
            'ru' => 'Справка с места работы',
            'tj' => 'Маълумотнома аз ҷойи кор',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'position', 'ru' => 'Должность', 'tj' => 'Вазифа'],
                ['key' => 'organization', 'ru' => 'Название организации', 'tj' => 'Номи ташкилот'],
                ['key' => 'work_period', 'ru' => 'Период работы', 'tj' => 'Давраи кор'],
                ['key' => 'purpose', 'ru' => 'Для чего нужна справка?', 'tj' => 'Маълумотнома барои чӣ лозим аст?'],
            ],
        ],
        'СПРАВКА_О_ДОХОДАХ' => [
            'ru' => 'Справка о доходах',
            'tj' => 'Маълумотнома дар бораи даромад',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'position', 'ru' => 'Должность', 'tj' => 'Вазифа'],
                ['key' => 'organization', 'ru' => 'Название организации', 'tj' => 'Номи ташкилот'],
                ['key' => 'income', 'ru' => 'Размер дохода', 'tj' => 'Маблағи даромад'],
                ['key' => 'period', 'ru' => 'Период дохода', 'tj' => 'Давраи даромад'],
                ['key' => 'purpose', 'ru' => 'Для чего нужна справка?', 'tj' => 'Маълумотнома барои чӣ лозим аст?'],
            ],
        ],
        'СПРАВКА_ПРОИЗВОЛЬНАЯ' => [
            'ru' => 'Справка произвольной формы',
            'tj' => 'Маълумотномаи озод',
            'fields' => [
                ['key' => 'organization', 'ru' => 'Кто выдаёт справку?', 'tj' => 'Маълумотномаро кӣ медиҳад?'],
                ['key' => 'recipient', 'ru' => 'Кому выдаётся справка?', 'tj' => 'Маълумотнома ба кӣ дода мешавад?'],
                ['key' => 'information', 'ru' => 'Какую информацию необходимо указать?', 'tj' => 'Кадом маълумот бояд нишон дода шавад?'],
                ['key' => 'purpose', 'ru' => 'Цель справки', 'tj' => 'Мақсади маълумотнома'],
            ],
        ],
        'ЖАЛОБА' => [
            'ru' => 'Жалоба',
            'tj' => 'Шикоят',
            'fields' => [
                ['key' => 'applicant', 'ru' => 'ФИО заявителя', 'tj' => 'Ном ва насаби аризадиҳанда'],
                ['key' => 'recipient', 'ru' => 'Кому направляется жалоба?', 'tj' => 'Шикоят ба кӣ равона мешавад?'],
                ['key' => 'problem', 'ru' => 'В чём заключается проблема?', 'tj' => 'Мушкилот дар чӣ аст?'],
                ['key' => 'date', 'ru' => 'Дата события', 'tj' => 'Санаи ҳодиса'],
                ['key' => 'request', 'ru' => 'Что вы просите сделать?', 'tj' => 'Шумо чӣ хоҳиш доред?'],
            ],
        ],
        'ОБЪЯСНИТЕЛЬНАЯ_ЗАПИСКА' => [
            'ru' => 'Объяснительная записка',
            'tj' => 'Тавзеҳнома / Баёнотнома',
            'fields' => [
                ['key' => 'employee_name', 'ru' => 'ФИО сотрудника', 'tj' => 'Ном ва насаби корманд'],
                ['key' => 'position', 'ru' => 'Должность', 'tj' => 'Вазифа'],
                ['key' => 'event', 'ru' => 'Что произошло?', 'tj' => 'Чӣ рӯй дод?'],
                ['key' => 'date', 'ru' => 'Дата события', 'tj' => 'Санаи ҳодиса'],
                ['key' => 'explanation', 'ru' => 'Объяснение сотрудника', 'tj' => 'Шарҳи корманд'],
            ],
        ],
        'ЗАЯВЛЕНИЕ_В_ГОСОРГАН' => [
            'ru' => 'Заявление в государственный орган',
            'tj' => 'Ариза ба мақомоти давлатӣ',
            'fields' => [
                ['key' => 'applicant', 'ru' => 'ФИО заявителя', 'tj' => 'Ном ва насаби аризадиҳанда'],
                ['key' => 'recipient', 'ru' => 'Государственный орган или должностное лицо', 'tj' => 'Мақомоти давлатӣ ё шахси мансабдор'],
                ['key' => 'request', 'ru' => 'Суть заявления', 'tj' => 'Мазмуни ариза'],
                ['key' => 'reason', 'ru' => 'Основание', 'tj' => 'Асос'],
            ],
        ],
        'ЗАЯВЛЕНИЕ_В_УЧЕБНОЕ_ЗАВЕДЕНИЕ' => [
            'ru' => 'Заявление в учебное заведение',
            'tj' => 'Ариза ба муассисаи таълимӣ',
            'fields' => [
                ['key' => 'applicant', 'ru' => 'ФИО заявителя', 'tj' => 'Ном ва насаби аризадиҳанда'],
                ['key' => 'institution', 'ru' => 'Название учебного заведения', 'tj' => 'Номи муассисаи таълимӣ'],
                ['key' => 'request', 'ru' => 'Что вы просите?', 'tj' => 'Шумо чӣ хоҳиш доред?'],
                ['key' => 'reason', 'ru' => 'Причина или основание', 'tj' => 'Сабаб ё асос'],
            ],
        ],
        'ПРЕТЕНЗИЯ' => [
            'ru' => 'Претензия',
            'tj' => 'Даъвонома',
            'fields' => [
                ['key' => 'applicant', 'ru' => 'ФИО или название организации заявителя', 'tj' => 'Ном ё номи ташкилоти даъвогар'],
                ['key' => 'recipient', 'ru' => 'Кому направляется претензия?', 'tj' => 'Даъво ба кӣ равона мешавад?'],
                ['key' => 'contract', 'ru' => 'Номер и дата договора, если есть', 'tj' => 'Рақам ва санаи шартнома, агар бошад'],
                ['key' => 'problem', 'ru' => 'Суть нарушения', 'tj' => 'Мазмуни вайронкунӣ'],
                ['key' => 'request', 'ru' => 'Требование', 'tj' => 'Талаб'],
            ],
        ],
        'СОГЛАСИЕ_ОТКАЗ' => [
            'ru' => 'Согласие или отказ',
            'tj' => 'Розигӣ ё раднома',
            'fields' => [
                ['key' => 'person', 'ru' => 'ФИО лица', 'tj' => 'Ном ва насаби шахс'],
                ['key' => 'subject', 'ru' => 'На что даётся согласие или отказ?', 'tj' => 'Розигӣ ё рад ба чӣ дода мешавад?'],
                ['key' => 'decision', 'ru' => 'Согласие или отказ', 'tj' => 'Розигӣ ё рад'],
                ['key' => 'reason', 'ru' => 'Причина, если необходимо', 'tj' => 'Сабаб, агар лозим бошад'],
            ],
        ],
    ];

    private const FIELD_EXAMPLES = [
        'employee_name' => ['ru' => 'Иванов Иван Иванович', 'tj' => 'Аминов Амир Аминович'],
        'applicant' => ['ru' => 'Иванов Иван Иванович', 'tj' => 'Аминов Амир Аминович'],
        'person' => ['ru' => 'Иванов Иван Иванович', 'tj' => 'Аминов Амир Аминович'],
        'principal_name' => ['ru' => 'Иванов Иван Иванович', 'tj' => 'Аминов Амир Аминович'],
        'representative_name' => ['ru' => 'Петров П.П.', 'tj' => 'Петров П.П.'],
        'position' => ['ru' => 'менеджер', 'tj' => 'омузгор'],
        'current_position' => ['ru' => 'менеджер', 'tj' => 'омузгор'],
        'new_position' => ['ru' => 'директор', 'tj' => 'директор'],
        'organization' => ['ru' => 'ООО Ромашка', 'tj' => 'ҶДММ Ромашка'],
        'institution' => ['ru' => 'МГУ', 'tj' => 'МДУ'],
        'start_date' => ['ru' => '01.10.2026', 'tj' => '01.10.2026'],
        'end_date' => ['ru' => '31.12.2026', 'tj' => '31.12.2026'],
        'last_work_date' => ['ru' => '30.09.2026', 'tj' => '30.09.2026'],
        'violation_date' => ['ru' => '15.09.2026', 'tj' => '15.09.2026'],
        'return_date' => ['ru' => '31.12.2026', 'tj' => '31.12.2026'],
        'child_birth_date' => ['ru' => '15.05.2020', 'tj' => '15.05.2020'],
        'valid_until' => ['ru' => '31.12.2026', 'tj' => '31.12.2026'],
        'date' => ['ru' => '15.09.2026', 'tj' => '15.09.2026'],
        'days' => ['ru' => '14', 'tj' => '14'],
        'amount' => ['ru' => '5000', 'tj' => '5000'],
        'price' => ['ru' => '10000', 'tj' => '10000'],
        'income' => ['ru' => '50000', 'tj' => '50000'],
        'current_salary' => ['ru' => '30000', 'tj' => '30000'],
        'new_salary' => ['ru' => '40000', 'tj' => '40000'],
        'reason' => ['ru' => 'по семейным обстоятельствам', 'tj' => 'бо сабабҳои оилавӣ'],
        'destination' => ['ru' => 'город Душанбе', 'tj' => 'шаҳри Душанбе'],
        'purpose' => ['ru' => 'переговоры с партнёрами', 'tj' => 'гуфтушунид бо шарикон'],
        'service' => ['ru' => 'консультационные услуги', 'tj' => 'хизматрасонии машваратӣ'],
        'term' => ['ru' => '12 месяцев', 'tj' => '12 моҳ'],
        'payment' => ['ru' => 'предоплата 50%', 'tj' => 'пешпардохт 50%'],
        'object' => ['ru' => 'квартира', 'tj' => 'хона'],
        'work_period' => ['ru' => 'с 2020 по 2026 год', 'tj' => 'аз 2020 то 2026'],
        'period' => ['ru' => 'за 2025 год', 'tj' => 'барои соли 2025'],
        'characteristics' => ['ru' => 'ответственный', 'tj' => 'масъулиятшинос'],
        'authority' => ['ru' => 'представлять интересы в суде', 'tj' => 'намояндагӣ дар суд'],
        'customer' => ['ru' => 'ООО Заказчик', 'tj' => 'ҶДММ Фармоишгар'],
        'provider' => ['ru' => 'ИП Иванов', 'tj' => 'СП Иванов'],
        'seller' => ['ru' => 'Иванов И.И.', 'tj' => 'Иванов И.И.'],
        'buyer' => ['ru' => 'Петров П.П.', 'tj' => 'Петров П.П.'],
        'landlord' => ['ru' => 'Сидоров С.С.', 'tj' => 'Сидоров С.С.'],
        'tenant' => ['ru' => 'Козлов К.К.', 'tj' => 'Козлов К.К.'],
        'lender' => ['ru' => 'Иванов И.И.', 'tj' => 'Иванов И.И.'],
        'borrower' => ['ru' => 'Петров П.П.', 'tj' => 'Петров П.П.'],
        'receiver' => ['ru' => 'Иванов И.И.', 'tj' => 'Иванов И.И.'],
        'payer' => ['ru' => 'Петров П.П.', 'tj' => 'Петров П.П.'],
        'recipient' => ['ru' => 'Директору ООО Ромашка', 'tj' => 'Ба директори ҶДММ Ромашка'],
        'information' => ['ru' => 'работает с 2020 года', 'tj' => 'аз соли 2020 кор мекунад'],
        'problem' => ['ru' => 'некачественный товар', 'tj' => 'моли бесифат'],
        'request' => ['ru' => 'прошу вернуть деньги', 'tj' => 'хоҳиш мекунам пулро баргардонам'],
        'event' => ['ru' => 'опоздал на работу', 'tj' => 'ба кор дер омад'],
        'explanation' => ['ru' => 'проспал', 'tj' => 'хобам бурд'],
        'contract' => ['ru' => '№123 от 01.01.2026', 'tj' => '№123 аз 01.01.2026'],
        'subject' => ['ru' => 'обработка персональных данных', 'tj' => 'коркарди маълумоти шахсӣ'],
        'decision' => ['ru' => 'согласен', 'tj' => 'розӣ'],
        'violation' => ['ru' => 'опоздание на 2 часа', 'tj' => 'дер омадан ба муддати 2 соат'],
        'child_name' => ['ru' => 'Иванова Анна', 'tj' => 'Аминова Заррина'],
    ];

    private const NAME_FIELDS = [
        'employee_name', 'applicant', 'person', 'principal_name',
        'representative_name', 'child_name', 'seller', 'buyer',
        'landlord', 'tenant', 'lender', 'borrower', 'receiver', 'payer',
    ];

    private const DATE_FIELDS = [
        'start_date', 'end_date', 'last_work_date', 'violation_date',
        'return_date', 'child_birth_date', 'valid_until', 'date',
    ];

    private const ORG_FIELDS = ['organization', 'institution'];
    private const POSITION_FIELDS = ['position', 'current_position', 'new_position'];

    private const TEXT_FIELDS = [
        'reason', 'purpose', 'problem', 'explanation', 'request',
        'characteristics', 'information', 'service', 'object', 'authority',
        'violation', 'event', 'subject', 'decision', 'payment', 'term',
        'work_period', 'period', 'contract', 'destination',
    ];

    private const VAGUE_POSITIONS = [
        'корманд', 'ба корманд', 'вазифа', 'должность', 'работник', 'сотрудник',
        'employee', 'worker', 'job', 'position', 'кор', 'ходам', 'ходим',
        'маълум нест', 'намедонам', 'не знаю', 'одатӣ',
    ];

    private const PREPOSITION_WORDS = [
        'ба', 'бо', 'аз', 'то', 'дар', 'барои', 'на', 'в', 'во', 'для', 'по',
        'за', 'от', 'с', 'со', 'к', 'у', 'о', 'об', 'из', 'до', 'при',
    ];

    private const SMALL_WORDS = [
        'ба', 'бо', 'аз', 'то', 'дар', 'барои', 'ва', 'и', 'на', 'в', 'во',
        'для', 'по', 'за', 'от', 'с', 'со', 'к', 'у', 'о', 'об', 'из', 'до',
        'при', 'е', 'the', 'of', 'and',
    ];

    private const RECIPIENT_REQUIRED_TYPES = [
        'ПРИЁМ_НА_РАБОТУ', 'УВОЛЬНЕНИЕ', 'ПЕРЕВОД', 'ОТПУСК_ОЧЕРЕДНОЙ',
        'ОТПУСК_БЕЗ_СОДЕРЖАНИЯ', 'ОТПУСК_ПО_УХОДУ_ЗА_РЕБЁНКОМ', 'ПРЕМИЯ',
        'ВЫГОВОР', 'ПОВЫШЕНИЕ_ЗАРПЛАТЫ', 'ИЗМЕНЕНИЕ_ДОЛЖНОСТИ', 'КОМАНДИРОВКА',
        'ОБЪЯСНИТЕЛЬНАЯ_ЗАПИСКА', 'ЗАЯВЛЕНИЕ_В_УЧЕБНОЕ_ЗАВЕДЕНИЕ',
    ];

    public function __construct()
    {
        $this->model = (string) config('services.ai.model', 'llama-3.3-70b-versatile');
        $this->url = (string) config('services.ai.base_url', 'https://api.groq.com/openai/v1/chat/completions');
    }

    public function chat(array $history, array $savedState = [], bool $isStuckInLoop = false): array
    {
        try {
            $latestUserMessage = $this->getLatestUserMessage($history);

            if ($latestUserMessage === '') {
                return $this->ask('ru', 'Какой документ вам нужен?');
            }

            $savedLang = $savedState['lang'] ?? null;
            if (!$savedLang) {
                $savedLang = $this->detectLanguage($latestUserMessage);
                if ($savedLang === 'other') {
                    return [
                        'action' => 'ask',
                        'reply' => 'Извините, я работаю только на русском и таджикском языках. Лутфан дархости худро бо забони русӣ ё тоҷикӣ нависед.',
                        'lang' => 'ru',
                        'doc_type' => null,
                        'memory' => [],
                        'last_questioned_field' => null,
                        'structure' => null,
                        'pending_type_clarification' => null,
                    ];
                }
            }

            $state = [
                'doc_type' => $savedState['doc_type'] ?? null,
                'lang' => $savedLang,
                'memory' => $savedState['memory'] ?? [],
                'last_questioned_field' => $savedState['last_questioned_field'] ?? null,
                'pending_type_clarification' => $savedState['pending_type_clarification'] ?? null,
            ];

            $typeJustDetected = false;

            if (!$state['doc_type'] && $state['pending_type_clarification']) {
                $resolved = $this->resolveTypeClarification(
                    $state['pending_type_clarification'],
                    $latestUserMessage,
                    $state['lang']
                );

                if ($resolved) {
                    $state['doc_type'] = $resolved;
                    $state['pending_type_clarification'] = null;
                    $typeJustDetected = true;
                    $state['last_questioned_field'] = null;
                } else {
                    $clarifyResponse = $this->handleSpecialDetection($state['pending_type_clarification'], $state['lang']);
                    if ($clarifyResponse !== null) {
                        $clarifyResponse['pending_type_clarification'] = $state['pending_type_clarification'];
                        return $clarifyResponse;
                    }
                }
            }

            if (!$state['doc_type']) {
                $detected = $this->detectDocumentType($latestUserMessage, $state['lang']);

                $clarifyResponse = $this->handleSpecialDetection($detected, $state['lang']);
                if ($clarifyResponse !== null) {
                    $clarifyResponse['pending_type_clarification'] = $detected;
                    return $clarifyResponse;
                }

                if (!$detected) {
                    return [
                        'action' => 'ask',
                        'reply' => $this->typeQuestion($state['lang']),
                        'lang' => $state['lang'],
                        'doc_type' => null,
                        'memory' => [],
                        'last_questioned_field' => null,
                        'structure' => null,
                        'pending_type_clarification' => null,
                    ];
                }
                $state['doc_type'] = $detected;
                $typeJustDetected = true;
                $state['last_questioned_field'] = null;
            }

            $clarify = null;
            $answeredLastQuestion = false;

            if ($state['last_questioned_field'] && !$this->isUserChangingTopic($latestUserMessage)) {
                $fieldKey = $state['last_questioned_field'];
                $normalized = $this->normalizeAndValidate($fieldKey, $latestUserMessage, $state['lang']);

                if ($normalized['valid']) {
                    $state['memory'][$fieldKey] = $normalized['value'];
                    foreach (($normalized['extra'] ?? []) as $extraKey => $extraValue) {
                        if (trim((string) ($state['memory'][$extraKey] ?? '')) === '') {
                            $state['memory'][$extraKey] = $extraValue;
                        }
                    }
                    $answeredLastQuestion = true;
                } else {
                    $clarify = ['field' => $fieldKey, 'error' => $normalized['error']];
                }
            }

            if (!$clarify && ($typeJustDetected || !$answeredLastQuestion || mb_strlen($latestUserMessage) > 40)) {
                $extracted = $this->extractAllFields($state['doc_type'], $latestUserMessage, $state['lang']);
                foreach ($extracted as $key => $rawValue) {
                    $current = $state['memory'][$key] ?? '';
                    if (trim((string) $current) === '') {
                        $n = $this->normalizeAndValidate($key, $rawValue, $state['lang']);
                        if ($n['valid']) {
                            $state['memory'][$key] = $n['value'];
                        }
                    }
                }
            }

            if ($clarify) {
                $response = $this->clarifyResponse($state, $clarify);
                $response['pending_type_clarification'] = null;
                return $response;
            }

            $missingFields = $this->getMissingFields($state['doc_type'], $state['memory']);

            if (!empty($missingFields)) {
                $nextField = reset($missingFields);
                $questionText = $this->questionForField($nextField, $state['lang']);

                if ($isStuckInLoop) {
                    $example = self::FIELD_EXAMPLES[$nextField['key']][$state['lang']] ?? '____________';
                    if ($state['lang'] === 'tj') {
                        $questionText .= "\n\nМасалан: " . $example;
                    } else {
                        $questionText .= "\n\nНапример: " . $example;
                    }
                }

                return [
                    'action' => 'ask',
                    'reply' => $questionText,
                    'lang' => $state['lang'],
                    'doc_type' => $state['doc_type'],
                    'memory' => $state['memory'],
                    'last_questioned_field' => $nextField['key'],
                    'structure' => null,
                    'pending_type_clarification' => null,
                ];
            }

            $state['memory'] = $this->finalizeAllData($state['doc_type'], $state['memory'], $state['lang']);

            $result = $this->generateDocument($state['doc_type'], $state['memory'], $state['lang']);
            $result['pending_type_clarification'] = null;
            return $result;

        } catch (\Throwable $e) {
            Log::error('AiService error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'action' => 'error',
                'reply' => 'Произошла ошибка при обработке документа.',
                'lang' => 'ru',
                'doc_type' => null,
                'memory' => [],
                'last_questioned_field' => null,
                'structure' => null,
                'pending_type_clarification' => null,
            ];
        }
    }

    private function resolveTypeClarification(string $pendingType, string $message, string $lang): ?string
    {
        $text = mb_strtolower($message);

        if ($pendingType === self::ASK_CERTIFICATE_TYPE) {
            if (preg_match('/(места работы|ҷойи кор|аз ҷойи кор|аз чо|ҷойи|ҷои|чойи|чо|работа|work|job)/ui', $text)) {
                return 'СПРАВКА_С_МЕСТА_РАБОТЫ';
            }
            if (preg_match('/(доход|даромад|ндфл|2-ндфл|зарплат|маош|salary|income)/ui', $text)) {
                return 'СПРАВКА_О_ДОХОДАХ';
            }
            if (preg_match('/(произвольн|озод|свободн|одатӣ|free|custom)/ui', $text)) {
                return 'СПРАВКА_ПРОИЗВОЛЬНАЯ';
            }
            return $this->resolveWithAI($pendingType, $message, $lang);
        }

        if ($pendingType === self::ASK_CONTRACT_TYPE) {
            if (preg_match('/(услуг|хизматрасонӣ|хизмат|service|оказани)/ui', $text)) {
                return 'ДОГОВОР_УСЛУГ';
            }
            if (preg_match('/(купл|продаж|харид|фурӯш|buy|sell|purchase)/ui', $text)) {
                return 'ДОГОВОР_КУПЛИ_ПРОДАЖИ';
            }
            if (preg_match('/(аренд|иҷора|икора|rent|найм|lease)/ui', $text)) {
                return 'ДОГОВОР_АРЕНДЫ';
            }
            if (preg_match('/(займ|қарз|карз|loan|кредит|credit|borrow)/ui', $text)) {
                return 'ДОГОВОР_ЗАЙМА';
            }
            return $this->resolveWithAI($pendingType, $message, $lang);
        }

        if ($pendingType === self::ASK_STATEMENT_TYPE) {
            if (preg_match('/(приём|прием|қабул|қабули|устроиться|hire|employ|job|work|кор|ба кор)/ui', $text)) {
                return 'ПРИЁМ_НА_РАБОТУ';
            }
            if (preg_match('/(увольн|аз кор рафтан|озод|уйти с работы|quit|resign|fire|рафтан)/ui', $text)) {
                return 'УВОЛЬНЕНИЕ';
            }
            if (preg_match('/(отпуск|рухсатӣ|leave|vacation)/ui', $text)) {
                return $this->detectLeaveType($text) ?: 'ОТПУСК_ОЧЕРЕДНОЙ';
            }
            if (preg_match('/(перевод|гузариш|вазифаи дигар|transfer|move)/ui', $text)) {
                return 'ПЕРЕВОД';
            }
            return $this->resolveWithAI($pendingType, $message, $lang);
        }

        if ($pendingType === self::ASK_LEAVE_TYPE) {
            if (preg_match('/(ежегодн|очередн|обычн|навбатӣ|меҳнатии навбатӣ|annual|regular)/ui', $text)) {
                return 'ОТПУСК_ОЧЕРЕДНОЙ';
            }
            if (preg_match('/(без содержан|за свой счет|за свой счёт|бемузд|бемуздӣ|unpaid|free)/ui', $text)) {
                return 'ОТПУСК_БЕЗ_СОДЕРЖАНИЯ';
            }
            if (preg_match('/(по уходу|за ребёнком|за ребенком|нигоҳубини кӯдак|кӯдак|child|care|parental)/ui', $text)) {
                return 'ОТПУСК_ПО_УХОДУ_ЗА_РЕБЁНКОМ';
            }
            return 'ОТПУСК_ОЧЕРЕДНОЙ';
        }

        return null;
    }

    private function resolveWithAI(string $pendingType, string $message, string $lang): ?string
    {
        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return null;
        }

        $options = [];
        if ($pendingType === self::ASK_CERTIFICATE_TYPE) {
            $options = [
                'СПРАВКА_С_МЕСТА_РАБОТЫ' => 'Справка с места работы / Маълумотнома аз ҷойи кор',
                'СПРАВКА_О_ДОХОДАХ' => 'Справка о доходах / Маълумотнома дар бораи даромад',
                'СПРАВКА_ПРОИЗВОЛЬНАЯ' => 'Справка произвольной формы / Маълумотномаи озод',
            ];
        } elseif ($pendingType === self::ASK_CONTRACT_TYPE) {
            $options = [
                'ДОГОВОР_УСЛУГ' => 'Договор оказания услуг / Шартномаи хизматрасонӣ',
                'ДОГОВОР_КУПЛИ_ПРОДАЖИ' => 'Договор купли-продажи / Шартномаи хариду фурӯш',
                'ДОГОВОР_АРЕНДЫ' => 'Договор аренды / Шартномаи иҷора',
                'ДОГОВОР_ЗАЙМА' => 'Договор займа / Шартномаи қарз',
            ];
        } elseif ($pendingType === self::ASK_STATEMENT_TYPE) {
            $options = [
                'ПРИЁМ_НА_РАБОТУ' => 'Заявление о приёме на работу / Ариза барои қабул ба кор',
                'УВОЛЬНЕНИЕ' => 'Заявление об увольнении / Ариза дар бораи аз кор рафтан',
                'ОТПУСК_ОЧЕРЕДНОЙ' => 'Заявление на отпуск / Ариза барои рухсатӣ',
                'ПЕРЕВОД' => 'Заявление о переводе / Ариза барои гузариш',
            ];
        } elseif ($pendingType === self::ASK_LEAVE_TYPE) {
            $options = [
                'ОТПУСК_ОЧЕРЕДНОЙ' => 'Ежегодный отпуск / Рухсатии меҳнатии навбатӣ',
                'ОТПУСК_БЕЗ_СОДЕРЖАНИЯ' => 'Отпуск без содержания / Рухсатии бемузд',
                'ОТПУСК_ПО_УХОДУ_ЗА_РЕБЁНКОМ' => 'Отпуск по уходу за ребёнком / Рухсатӣ нигоҳубини кӯдак',
            ];
        }

        if (empty($options)) {
            return null;
        }

        $optionsList = [];
        foreach ($options as $key => $desc) {
            $optionsList[] = "- {$key} = {$desc}";
        }

        $prompt = <<<PROMPT
Пользователь ответил на уточняющий вопрос. Определи какой тип документа он выбрал.

Ответ пользователя: "{$message}"
Язык: {$lang}

Доступные варианты:
{$this->joinLines($optionsList)}

Правила:
1. Выбери ОДИН тип из списка выше
2. Понимай опечатки и неформальный язык
3. Если не можешь определить — верни null
4. Верни только JSON

Формат:
{"doc_type": "ТИП_ДОКУМЕНТА"}
или
{"doc_type": null}
PROMPT;

        $result = $this->askAI($prompt, 300);

        if ($result && isset($result['doc_type']) && is_string($result['doc_type'])) {
            $type = $result['doc_type'];
            if (isset($options[$type])) {
                return $type;
            }
        }

        return null;
    }

    private function handleSpecialDetection(?string $detected, string $lang): ?array
    {
        if ($detected === self::ASK_CONTRACT_TYPE) {
            $reply = $lang === 'tj'
                ? "Кадом шартнома ба шумо лозим аст?\n- Шартномаи хизматрасонӣ (договор услуг)\n- Шартномаи хариду фурӯш (купля-продажа)\n- Шартномаи иҷора (аренда)\n- Шартномаи қарз (займ)"
                : "Какой договор вам нужен?\n- Договор оказания услуг\n- Договор купли-продажи\n- Договор аренды\n- Договор займа";
            return $this->askWithSpecialType($lang, $reply);
        }

        if ($detected === self::ASK_STATEMENT_TYPE) {
            $reply = $lang === 'tj'
                ? "Кадом ариза ба шумо лозим аст?\n- Ариза барои қабул ба кор\n- Ариза дар бораи аз кор рафтан\n- Ариза барои рухсатӣ\n- Ариза барои гузариш ба вазифаи дигар"
                : "Какое заявление вам нужно?\n- Заявление о приёме на работу\n- Заявление об увольнении\n- Заявление на отпуск\n- Заявление о переводе";
            return $this->askWithSpecialType($lang, $reply);
        }

        if ($detected === self::ASK_LEAVE_TYPE) {
            $reply = $lang === 'tj'
                ? "Кадом рухсатӣ ба шумо лозим аст?\n- Рухсатии меҳнатии навбатӣ (ежегодный)\n- Рухсатии бемузд (без содержания)\n- Рухсатӣ нигоҳубини кӯдак (по уходу за ребёнком)"
                : "Какой отпуск вам нужен?\n- Ежегодный отпуск\n- Отпуск без сохранения заработной платы\n- Отпуск по уходу за ребёнком";
            return $this->askWithSpecialType($lang, $reply);
        }

        if ($detected === self::ASK_CERTIFICATE_TYPE) {
            $reply = $lang === 'tj'
                ? "Кадом маълумотнома ба шумо лозим аст?\n- Маълумотнома аз ҷойи кор (с места работы)\n- Маълумотнома дар бораи даромад (о доходах)\n- Маълумотномаи озод (произвольная)"
                : "Какая справка вам нужна?\n- Справка с места работы\n- Справка о доходах\n- Справка произвольной формы";
            return $this->askWithSpecialType($lang, $reply);
        }

        return null;
    }

    private function askWithSpecialType(string $lang, string $reply): array
    {
        return [
            'action' => 'ask',
            'reply' => $reply,
            'lang' => $lang,
            'doc_type' => null,
            'memory' => [],
            'last_questioned_field' => null,
            'structure' => null,
            'pending_type_clarification' => null,
        ];
    }

    private function finalizeAllData(string $docType, array $memory, string $lang): array
    {
        foreach (self::ORG_FIELDS as $orgKey) {
            if (isset($memory[$orgKey]) && trim((string) $memory[$orgKey]) !== '') {
                $memory[$orgKey] = $this->fixCompanyName($memory[$orgKey]);
            }
        }

        $apiKey = $this->getApiKey();
        if (!$apiKey || empty($memory)) {
            return $memory;
        }

        $document = self::DOCUMENTS[$docType] ?? null;
        if (!$document) {
            return $memory;
        }

        $fieldsData = [];
        foreach ($document['fields'] as $field) {
            $key = $field['key'];
            if (isset($memory[$key]) && trim((string) $memory[$key]) !== '') {
                $label = $field[$lang] ?? $field['ru'];
                $fieldsData[] = '- ' . $key . ' (' . $label . '): "' . $memory[$key] . '"';
            }
        }
        if (isset($memory['recipient']) && trim((string) $memory['recipient']) !== '') {
            $rd = $this->getRecipientFieldDef();
            $fieldsData[] = '- recipient (' . $rd[$lang] . '): "' . $memory['recipient'] . '"';
        }

        if (empty($fieldsData)) {
            return $memory;
        }

        $prompt = <<<PROMPT
Ты редактор-корректор официальных документов. Проверь и исправь ВСЕ данные.
Язык: {$lang}
Тип документа: {$docType}

Собранные данные пользователя:
{$this->joinLines($fieldsData)}

КРИТИЧЕСКИ ВАЖНЫЕ ПРАВИЛА:

1. НАЗВАНИЯ ОРГАНИЗАЦИЙ И КОМПАНИЙ — НЕ МЕНЯЙ НИКОГДА!
   - Оставь ТОЧНО как написал пользователь
   - "Алиф" → "Алиф" (НЕ "Алӣ", НЕ "Алифӣ")
   - "Tcell" → "Tcell" (НЕ "Тселл")
   - НЕ добавляй кавычки
   - НЕ исправляй бренды

2. ОПЕЧАТКИ В ИМЕНАХ ЛЮДЕЙ:
   - "Аммир" → "Амир" (убери двойные буквы)
   - "Амри" → "Амир"
   - "Амивон" → "Аминов"
   - Все имена с большой буквы

3. ГРАММАТИКУ И ОРФОГРАФИЮ в текстах:
   - "онки" → "он ки"
   - "мехозам" → "мехоҳам"
   - "чойи" → "ҷойи"
   - Смысл НЕ меняй

4. ФОРМАТИРОВАНИЕ:
   - Даты в формате ДД.ММ.ГГГГ
   - "03.04.26" → "03.04.2026"

5. Поле recipient приведи к виду должности/лица

ПРАВИЛА:
- НЕ ПРИДУМЫВАЙ новую информацию
- НЕ МЕНЯЙ смысл текстов
- НАЗВАНИЯ КОМПАНИЙ ОСТАВЬ БЕЗ ИЗМЕНЕНИЙ
- Если данные правильные — оставь как есть
- Верни ВСЕ поля из исходных данных

Верни СТРОГО JSON с ИСПРАВЛЕННЫМИ значениями:
{
PROMPT;

        $first = true;
        foreach ($memory as $key => $value) {
            if (!$first) $prompt .= ",";
            $prompt .= "\n    \"" . $key . "\": \"значение\"";
            $first = false;
        }
        $prompt .= "\n}";

        $result = $this->askAI($prompt, 1000);

        if ($result && is_array($result)) {
            $corrected = [];
            foreach ($memory as $key => $originalValue) {
                if (in_array($key, self::ORG_FIELDS, true)) {
                    $corrected[$key] = $originalValue;
                    continue;
                }

                if (isset($result[$key]) && is_string($result[$key]) && trim($result[$key]) !== '') {
                    $corrected[$key] = trim($result[$key]);
                } else {
                    $corrected[$key] = $originalValue;
                }
            }
            return $corrected;
        }

        return $memory;
    }

    private function fixCompanyName(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return $value;
        }

        $knownCorrect = ['Алиф', 'Alif', 'Tcell', 'Megafon', 'Babilon', 'Eskhata'];
        foreach ($knownCorrect as $correct) {
            if (mb_strtolower($value) === mb_strtolower($correct)) {
                return $correct;
            }
        }

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return $this->capitalizeSmart($value);
        }

        $prompt = <<<PROMPT
Пользователь написал название компании/организации с возможной опечаткой.
Исправь ТОЛЬКО опечатку, НЕ МЕНЯЙ название.

Написано: "{$value}"

ПРИМЕРЫ ПРАВИЛЬНЫХ НАЗВАНИЙ:
- "Алиф" (не "Алӣ", не "Алифӣ", не "Алф")
- "Alif" (латиницей)
- "Tcell" (не "Тселл")
- "Megafon" (не "Мегафон" если написано латиницей)
- "Babilon"
- "Eskhata"

ПРАВИЛА:
1. Если это известная компания — напиши её ПРАВИЛЬНОЕ название
2. Если это обычная компания — просто исправь опечатку и сделай первую букву большой
3. НЕ добавляй кавычки
4. НЕ добавляй "ООО", "ҶДММ" и т.д. если их не было
5. Верни ТОЛЬКО исправленное название

Верни JSON: {"name": "исправленное название"}
PROMPT;

        $result = $this->askAI($prompt, 300);

        if ($result && isset($result['name']) && is_string($result['name']) && trim($result['name']) !== '') {
            return trim($result['name']);
        }

        return $this->capitalizeSmart($value);
    }

    private function normalizeAndValidate(string $fieldKey, string $rawValue, string $lang): array
    {
        $value = trim(preg_replace('/\s+/u', ' ', $rawValue));

        if ($value === '') {
            return ['valid' => false, 'value' => '', 'error' => 'empty'];
        }

        $junk = ['да', 'нет', 'ок', 'okay', 'ok', 'ха', 'не', 'не знаю', 'намедонам',
                 'намедон', 'без понятия', 'ҳа', 'неҳ', 'ҳ', '?', '.', '-', '---', 'да нет'];
        if (in_array(mb_strtolower($value), $junk, true)) {
            return ['valid' => false, 'value' => '', 'error' => 'junk'];
        }

        if (in_array($fieldKey, self::DATE_FIELDS, true)) {
            $normalized = $this->normalizeDate($value);
            if ($normalized === null) {
                return ['valid' => false, 'value' => '', 'error' => 'bad_date'];
            }
            return ['valid' => true, 'value' => $normalized, 'error' => null];
        }

        if (in_array($fieldKey, self::POSITION_FIELDS, true)) {
            $lower = mb_strtolower($value);
            $words = preg_split('/\s+/u', $lower);
            $firstWord = $words[0] ?? '';

            if (in_array($lower, self::VAGUE_POSITIONS, true) || in_array($firstWord, self::PREPOSITION_WORDS, true)) {
                return ['valid' => false, 'value' => '', 'error' => 'vague_position'];
            }

            $corrected = $this->correctSpelling($value, $lang);
            return ['valid' => true, 'value' => $corrected ?: $value, 'error' => null];
        }

        if (in_array($fieldKey, self::NAME_FIELDS, true)) {
            $words = array_values(array_filter(preg_split('/[\s-]+/u', $value)));
            if (count($words) < 2) {
                return ['valid' => false, 'value' => '', 'error' => 'short_name'];
            }
            return ['valid' => true, 'value' => $this->capitalizeSmart($value), 'error' => null];
        }

        if (in_array($fieldKey, self::ORG_FIELDS, true)) {
            $split = $this->splitOrganizationAndRecipient($value, $lang);
            $extra = [];
            if (!empty($split['recipient'])) {
                $extra['recipient'] = $split['recipient'];
            }
            return ['valid' => true, 'value' => $split['organization'], 'error' => null, 'extra' => $extra];
        }

        if ($fieldKey === 'recipient') {
            return ['valid' => true, 'value' => $this->capitalizeSmart($value), 'error' => null];
        }

        if (in_array($fieldKey, self::TEXT_FIELDS, true) && mb_strlen($value) >= 3) {
            $corrected = $this->correctSpelling($value, $lang);
            return ['valid' => true, 'value' => $corrected ?: $value, 'error' => null];
        }

        return ['valid' => true, 'value' => $value, 'error' => null];
    }

    private function splitOrganizationAndRecipient(string $value, string $lang): array
    {
        $default = ['organization' => $this->capitalizeSmart($value), 'recipient' => null];

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return $default;
        }

        $prompt = <<<PROMPT
Пользователя спросили: назовите организацию (или куда подаётся документ).
Он ответил: "{$value}"

Раздели ответ на 2 части:
1. organization — ТОЛЬКО название организации (без слов-адресатов).
2. recipient — кому адресовано (должность или лицо), ЕСЛИ это упомянуто в ответе. Если не упомянуто — null.

Примеры:
- "ташкилоти алиф ба шахси раис" → {"organization": "Ташкилоти Алиф", "recipient": "Раис"}
- "ООО Ромашка" → {"organization": "ООО Ромашка", "recipient": null}
- "директору школы №5" → {"organization": "Школа №5", "recipient": "Директор"}
- "ба раиси компания алиф" → {"organization": "Компания Алиф", "recipient": "Раис"}

Правила:
- НЕ придумывай. Бери только то, что есть в ответе.
- Верни только JSON.

Формат: {"organization": "...", "recipient": "..." или null}
PROMPT;

        $result = $this->askAI($prompt, 300);

        if ($result && is_array($result)) {
            $org = isset($result['organization']) && is_string($result['organization']) && trim($result['organization']) !== ''
                ? trim($result['organization']) : null;
            $rec = isset($result['recipient']) && is_string($result['recipient']) && trim($result['recipient']) !== ''
                ? trim($result['recipient']) : null;

            if ($org !== null) {
                return [
                    'organization' => $this->capitalizeSmart($org),
                    'recipient' => $rec !== null ? $this->capitalizeSmart($rec) : null,
                ];
            }
        }

        return $default;
    }

    private function getRecipientFieldDef(): array
    {
        return [
            'key' => 'recipient',
            'ru' => 'Кому адресован документ (должность или ФИО руководителя)?',
            'tj' => 'Ба кӣ ҳуҷҷат адресат мешавад (мансаб ё номи роҳбар)?',
        ];
    }

    private function capitalizeSmart(string $text): string
    {
        $parts = preg_split('/(\s+|-)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';

        foreach ($parts as $part) {
            if ($part === '' || preg_match('/^[\s-]+$/u', $part)) {
                $result .= $part;
                continue;
            }
            $clean = preg_replace('/[^\p{L}]/u', '', $part);
            if (mb_strlen($clean) <= 6 && mb_strlen($clean) >= 2 && preg_match('/^\p{Lu}+$/u', $clean)) {
                $result .= $part;
                continue;
            }
            if (in_array(mb_strtolower($clean), self::SMALL_WORDS, true)) {
                $result .= mb_strtolower($part);
                continue;
            }
            $result .= mb_convert_case($part, MB_CASE_TITLE, 'UTF-8');
        }

        return $result;
    }

    private function normalizeDate(string $value): ?string
    {
        $v = trim($value);

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/u', $v, $m)) {
            return $this->makeDate((int) $m[3], (int) $m[2], (int) $m[1]);
        }

        $v = str_replace(['/', '-'], '.', $v);

        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/u', $v, $m)) {
            return $this->makeDate((int) $m[1], (int) $m[2], (int) $m[3]);
        }

        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{2})$/u', $v, $m)) {
            $year = (int) $m[3];
            $year += ($year < 70) ? 2000 : 1900;
            return $this->makeDate((int) $m[1], (int) $m[2], $year);
        }

        if (preg_match('/^(\d{1,2})\.(\d{1,2})$/u', $v, $m)) {
            return $this->makeDate((int) $m[1], (int) $m[2], (int) now()->format('Y'));
        }

        $months = [
            'янв' => 1, 'фев' => 2, 'мар' => 3, 'апр' => 4, 'май' => 5, 'маи' => 5,
            'июн' => 6, 'июл' => 7, 'авг' => 8, 'сен' => 9, 'окт' => 10, 'ноя' => 11, 'дек' => 12,
        ];
        if (preg_match('/^(\d{1,2})\s+([а-яёҳҷқғӣӯa-z]+)\.?\s+(\d{4})$/ui', $v, $m)) {
            $monthWord = mb_strtolower($m[2]);
            foreach ($months as $prefix => $num) {
                if (str_starts_with($monthWord, $prefix) || str_starts_with($prefix, $monthWord)) {
                    return $this->makeDate((int) $m[1], $num, (int) $m[3]);
                }
            }
        }

        return null;
    }

    private function makeDate(int $day, int $month, int $year): ?string
    {
        if (!checkdate($month, $day, $year)) {
            return null;
        }
        return sprintf('%02d.%02d.%04d', $day, $month, $year);
    }

    private function correctSpelling(string $value, string $lang): ?string
    {
        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return null;
        }

        $prompt = <<<PROMPT
Ты корректор. Исправь орфографические и грамматические ошибки.
Язык: {$lang}
Текст: "{$value}"

ПРАВИЛА:
1. Исправь ТОЛЬКО орфографию и грамматику
2. НЕ меняй смысл
3. НЕ добавляй новую информацию
4. НЕ удаляй информацию
5. НЕ переводи
6. Если текст правильный — верни без изменений

Верни JSON: {"value": "исправленный текст"}
PROMPT;

        $result = $this->askAI($prompt, 400);

        if ($result && isset($result['value']) && is_string($result['value']) && trim($result['value']) !== '') {
            return trim($result['value']);
        }

        return null;
    }

    private function clarifyResponse(array $state, array $clarify): array
    {
        $fieldKey = $clarify['field'];
        $error = $clarify['error'];
        $lang = $state['lang'];

        $fieldDef = null;
        foreach (self::DOCUMENTS[$state['doc_type']]['fields'] ?? [] as $f) {
            if ($f['key'] === $fieldKey) {
                $fieldDef = $f;
                break;
            }
        }
        if (!$fieldDef && $fieldKey === 'recipient') {
            $fieldDef = $this->getRecipientFieldDef();
        }

        $baseQuestion = $fieldDef ? $this->questionForField($fieldDef, $lang) : '';
        $example = self::FIELD_EXAMPLES[$fieldKey][$lang] ?? '____________';

        if ($lang === 'tj') {
            $hint = match ($error) {
                'bad_date' => "Санаро нодуруст навиштед. Лутфан дар формати РР.ММ.СССС нависед. Масалан: {$example}",
                'short_name' => "Лутфан ном ва насабро пурра нависед (камаш ду калима). Масалан: {$example}",
                'vague_position' => "Лутфан вазифаро дақиқ нависед. Масалан: омузгор, менеҷер, ҳисобчӣ, муҳандис.",
                'junk' => "Лутфан ҷавоби мушаххас диҳед, на танҳо «ҳа» ё «не».",
                'empty' => "Ин майдон холӣ аст. Лутфан пур кунед.",
                default => "Лутфан дақиқтар нависед. Масалан: {$example}",
            };
        } else {
            $hint = match ($error) {
                'bad_date' => "Дата указана неверно. Укажите в формате ДД.ММ.ГГГГ. Например: {$example}",
                'short_name' => "Укажите ФИО полностью (минимум два слова). Например: {$example}",
                'vague_position' => "Укажите должность конкретно. Например: омузгор, менеджер, бухгалтер, инженер.",
                'junk' => "Ответьте конкретно, а не просто «да» или «нет».",
                'empty' => "Это поле пустое. Заполните его.",
                default => "Уточните ваш ответ. Например: {$example}",
            };
        }

        $reply = $baseQuestion !== '' ? $hint . "\n\n" . $baseQuestion : $hint;

        return [
            'action' => 'ask',
            'reply' => $reply,
            'lang' => $lang,
            'doc_type' => $state['doc_type'],
            'memory' => $state['memory'],
            'last_questioned_field' => $fieldKey,
            'structure' => null,
            'pending_type_clarification' => null,
        ];
    }

    private function isUserChangingTopic(string $message): bool
    {
        $text = mb_strtolower($message);
        $changeWords = [
            'другой документ', 'другой тип', 'передумал', 'не то', 'начать сначала',
            'ҳуҷҷати дигар', 'навъи дигар', 'аз нав',
        ];
        foreach ($changeWords as $word) {
            if (mb_strpos($text, $word) !== false) {
                return true;
            }
        }
        return mb_strlen($message) > 200;
    }

    private function getLatestUserMessage(array $history): string
    {
        foreach (array_reverse($history) as $message) {
            if (($message['role'] ?? '') === 'user') {
                return trim((string) ($message['content'] ?? ''));
            }
        }
        return '';
    }

    private function detectLanguage(string $text): string
    {
        if (preg_match('/[ҳҷқғӣӯҲҶҚҒӢӮ]/u', $text)) {
            return 'tj';
        }

        $lower = mb_strtolower($text);

        $tjWords = [
            'ариза', 'ман', 'мехоҳам', 'лозим', 'ҳуҷҷат', 'кор',
            'барои', 'шумо', 'ном', 'насаб', 'маълумотнома',
            'рухсатӣ', 'ваколатнома', 'забонхат', 'шикоят',
            'тавзеҳнома', 'даъвонома', 'розигӣ', 'раднома',
            'тавсифнома', 'мукофот', 'маош', 'иҷора', 'қарз',
            'фармоишгар', 'иҷрокунанда', 'харидор', 'фурӯшанда',
            'салом', 'раҳмат', 'кумак', 'хоҳиш', 'сабаб',
            'шартнома', 'қабул', 'гузариш', 'бемузд', 'навбатӣ',
            'баёнотнома', 'баёнот', 'баенотном', 'байнотном',
        ];

        foreach ($tjWords as $word) {
            if (preg_match('/(?<!\p{L})' . preg_quote($word, '/') . '(?!\p{L})/u', $lower)) {
                return 'tj';
            }
        }

        $ruWords = [
            'заявление', 'отпуск', 'доверенность', 'договор', 'справка',
            'жалоба', 'претензия', 'расписка', 'характеристика',
            'командировка', 'премия', 'выговор', 'перевод',
            'увольнение', 'приём', 'прием', 'аренда', 'займ',
            'купля', 'продажа', 'услуга', 'доход', 'зарплата',
            'должность', 'организация', 'сотрудник', 'работник',
            'объяснительная',
        ];

        foreach ($ruWords as $word) {
            if (preg_match('/(?<!\p{L})' . preg_quote($word, '/') . '(?!\p{L})/u', $lower)) {
                return 'ru';
            }
        }

        if (preg_match('/[а-яА-ЯёЁ]/u', $text)) {
            return 'ru';
        }

        return 'other';
    }

    private function detectDocumentType(string $message, string $lang): ?string
    {
        $localResult = $this->detectDocumentTypeLocal($message);
        if ($localResult) {
            return $localResult;
        }

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return null;
        }

        $available = [];
        foreach (self::DOCUMENTS as $key => $document) {
            $docName = $document[$lang] ?? $document['ru'];
            $available[] = $key . ' = ' . $docName;
        }

        $prompt = <<<PROMPT
Ты определяешь только тип документа. Язык: {$lang}

Сообщение пользователя:
{$message}

Доступные типы:
{$this->joinLines($available)}

Правила:
1. Выбери только один тип из списка.
2. Не придумывай новый тип.
3. Понимай опечатки (например "баёонотнома" = ОБЪЯСНИТЕЛЬНАЯ_ЗАПИСКА).
4. Если невозможно определить — верни null.
5. Верни только JSON.

Формат:
{"doc_type": "ПРИЁМ_НА_РАБОТУ"}
или
{"doc_type": null}
PROMPT;

        $result = $this->askAI($prompt, 300);

        if (!$result) {
            return null;
        }

        $type = $result['doc_type'] ?? null;
        return (is_string($type) && isset(self::DOCUMENTS[$type])) ? $type : null;
    }

    private function detectDocumentTypeLocal(string $message): ?string
    {
        $text = mb_strtolower($message);

        $contractTypes = [
            'ДОГОВОР_УСЛУГ' => [
                'ru' => ['услуг', 'хизматрасонӣ', 'service', 'оказани'],
                'tj' => ['хизматрасонӣ', 'хизмат', 'хизмати'],
            ],
            'ДОГОВОР_КУПЛИ_ПРОДАЖИ' => [
                'ru' => ['купл', 'продаж', 'харид', 'фурӯш'],
                'tj' => ['харид', 'фурӯш', 'хариду'],
            ],
            'ДОГОВОР_АРЕНДЫ' => [
                'ru' => ['аренд', 'иҷора', 'rent', 'найм'],
                'tj' => ['иҷора', 'икора'],
            ],
            'ДОГОВОР_ЗАЙМА' => [
                'ru' => ['займ', 'қарз', 'loan', 'кредит'],
                'tj' => ['қарз', 'карз'],
            ],
        ];

        $hasContractWord = preg_match('/\b(договор|шартнома|шартномаҳо)\b/ui', $text);

        if ($hasContractWord) {
            foreach ($contractTypes as $type => $keywordsByLang) {
                foreach ($keywordsByLang as $keywords) {
                    foreach ($keywords as $keyword) {
                        if (mb_strpos($text, $keyword) !== false) {
                            return $type;
                        }
                    }
                }
            }
            return self::ASK_CONTRACT_TYPE;
        }

        foreach ($contractTypes as $type => $keywordsByLang) {
            $matchedKeywords = 0;
            foreach ($keywordsByLang as $keywords) {
                foreach ($keywords as $keyword) {
                    if (mb_strpos($text, $keyword) !== false) {
                        $matchedKeywords++;
                    }
                }
            }
            if ($matchedKeywords >= 2) {
                return $type;
            }
        }

        $statementSpecific = [
            'ПРИЁМ_НА_РАБОТУ' => ['принять на работу', 'прием на работу', 'приём на работу', 'устроиться на работу', 'қабул ба кор', 'қабул кардан', 'ба кор даромадан', 'қабули кор', 'ба кор'],
            'УВОЛЬНЕНИЕ' => ['уволиться', 'увольнение', 'уволить', 'уйти с работы', 'аз кор рафтан', 'озод кардан', 'рафтан аз кор'],
            'ПЕРЕВОД' => ['перевод на другую должность', 'перевод', 'гузариш ба вазифа', 'гузариш', 'перевестись'],
            'ПОВЫШЕНИЕ_ЗАРПЛАТЫ' => ['повысить зарплату', 'повышение зарплаты', 'повысить заработную плату', 'зиёд кардани маош', 'маошро зиёд'],
            'ИЗМЕНЕНИЕ_ДОЛЖНОСТИ' => ['изменение должности', 'тағйири вазифа', 'смена должности'],
            'ЗАЯВЛЕНИЕ_В_УЧЕБНОЕ_ЗАВЕДЕНИЕ' => ['заявление в учебное заведение', 'заявление в университет', 'ариза ба муассисаи таълимӣ', 'ариза ба донишгоҳ', 'поступление'],
            'ЗАЯВЛЕНИЕ_В_ГОСОРГАН' => ['заявление в государственный орган', 'заявление в госорган', 'ариза ба мақомоти давлатӣ', 'акимат', 'хокимият'],
        ];

        $hasStatementWord = preg_match('/\b(заявление|ариза|аризаҳо)\b/ui', $text);

        if ($hasStatementWord) {
            if (preg_match('/(отпуск|рухсатӣ)/ui', $text)) {
                return $this->detectLeaveType($text);
            }
            foreach ($statementSpecific as $type => $keywords) {
                foreach ($keywords as $keyword) {
                    if (mb_strpos($text, $keyword) !== false) {
                        return $type;
                    }
                }
            }
            return self::ASK_STATEMENT_TYPE;
        }

        if (preg_match('/\b(отпуск|рухсатӣ|рухсати)\b/ui', $text)) {
            return $this->detectLeaveType($text);
        }

        if (preg_match('/\b(справк|маълумотнома)\b/ui', $text)) {
            if (preg_match('/(места работы|ҷойи кор|аз ҷойи кор)/ui', $text)) {
                return 'СПРАВКА_С_МЕСТА_РАБОТЫ';
            }
            if (preg_match('/(доход|даромад|ндфл|2-ндфл)/ui', $text)) {
                return 'СПРАВКА_О_ДОХОДАХ';
            }
            if (preg_match('/(произвольн|озод|свободн)/ui', $text)) {
                return 'СПРАВКА_ПРОИЗВОЛЬНАЯ';
            }
            return self::ASK_CERTIFICATE_TYPE;
        }

        $specificTypes = [
            'ПРЕМИЯ' => ['премия', 'премировать', 'премирование', 'мукофот', 'мукофотонидан', 'бонус', 'bonus'],
            'ВЫГОВОР' => ['выговор', 'дисциплинарное взыскание', 'ҷазои интизомӣ', 'ҷазо', 'танбеҳ', 'наказание'],
            'КОМАНДИРОВКА' => ['командировка', 'служебная поездка', 'сафари хизматӣ', 'сафар', 'командиров'],
            'ХАРАКТЕРИСТИКА' => ['характеристика', 'тавсифнома', 'тавсиф', 'reference'],
            'ДОВЕРЕННОСТЬ' => ['доверенность', 'ваколатнома', 'ваколат', 'power of attorney'],
            'РАСПИСКА' => ['расписка', 'забонхат', 'забон', 'receipt', 'расписк'],
            'ЖАЛОБА' => ['жалоба', 'пожаловаться', 'шикоят', 'жаловаться', 'шикоятнома', 'complaint'],
            'ОБЪЯСНИТЕЛЬНАЯ_ЗАПИСКА' => ['объяснительная', 'объяснительная записка', 'тавзеҳнома', 'баёнотнома', 'баенотном', 'байнотном', 'баёнот', 'explanation', 'объяснительн'],
            'ПРЕТЕНЗИЯ' => ['претензия', 'даъвонома', 'даъво', 'claim'],
            'СОГЛАСИЕ_ОТКАЗ' => ['согласие', 'отказ', 'розигӣ', 'раднома', 'розиг', 'рад'],
        ];

        foreach ($specificTypes as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($text, $keyword) !== false) {
                    return $type;
                }
            }
        }

        return null;
    }

    private function detectLeaveType(string $text): ?string
    {
        if (preg_match('/(ежегодн|очередн|обычн|навбатӣ|меҳнатии навбатӣ)/ui', $text)) {
            return 'ОТПУСК_ОЧЕРЕДНОЙ';
        }
        if (preg_match('/(без содержан|за свой счет|за свой счёт|бемузд|бемуздӣ)/ui', $text)) {
            return 'ОТПУСК_БЕЗ_СОДЕРЖАНИЯ';
        }
        if (preg_match('/(по уходу|за ребёнком|за ребенком|нигоҳубини кӯдак|кӯдак)/ui', $text)) {
            return 'ОТПУСК_ПО_УХОДУ_ЗА_РЕБЁНКОМ';
        }
        return self::ASK_LEAVE_TYPE;
    }

    private function getMissingFields(string $docType, array $memory): array
    {
        $document = self::DOCUMENTS[$docType] ?? null;
        if (!$document) {
            return [];
        }

        $missing = [];
        foreach ($document['fields'] as $field) {
            $key = $field['key'];
            if (!isset($memory[$key]) || trim((string) $memory[$key]) === '') {
                $missing[$key] = $field;
            }
        }

        if (in_array($docType, self::RECIPIENT_REQUIRED_TYPES, true)
            && trim((string) ($memory['recipient'] ?? '')) === '') {
            $rd = $this->getRecipientFieldDef();
            $newMissing = [];
            foreach ($missing as $k => $f) {
                $newMissing[$k] = $f;
                if (in_array($k, self::ORG_FIELDS, true)) {
                    $newMissing['recipient'] = $rd;
                }
            }
            if (!isset($newMissing['recipient'])) {
                $newMissing['recipient'] = $rd;
            }
            $missing = $newMissing;
        }

        return $missing;
    }

    private function questionForField(array $field, string $lang): string
    {
        return $field[$lang] ?? $field['ru'];
    }

    private function extractAllFields(string $docType, string $message, string $lang): array
    {
        $document = self::DOCUMENTS[$docType] ?? null;
        if (!$document) {
            return [];
        }

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return [];
        }

        $fieldsList = [];
        foreach ($document['fields'] as $field) {
            $fieldsList[] = "- " . $field['key'] . " (" . $field[$lang] . ")";
        }
        if (in_array($docType, self::RECIPIENT_REQUIRED_TYPES, true)) {
            $rd = $this->getRecipientFieldDef();
            $fieldsList[] = "- recipient (" . $rd[$lang] . ")";
        }

        $prompt = <<<PROMPT
Извлеки данные из сообщения пользователя для заполнения документа.
Язык: {$lang}
Тип документа: {$docType}

Сообщение пользователя:
{$message}

Нужные поля:
{$this->joinLines($fieldsList)}

Правила:
1. Извлеки ТОЛЬКО те данные, которые ЕСТЬ в сообщении.
2. НЕ ПРИДУМЫВАЙ данные, которых нет в сообщении.
3. Если какое-то поле не упомянуто — не добавляй его в результат.
4. Возвращай точные значения из сообщения.
5. Исправь явные опечатки и орфографические ошибки в значениях, сохраняя язык и смысл.
6. Имена людей и названия организаций пиши с большой буквы.
7. Верни только JSON с найденными полями.

Формат:
{"field_name": "значение", "another_field": "значение"}

Если в сообщении нет никаких данных — верни пустой объект: {}
PROMPT;

        $result = $this->askAI($prompt, 500);

        if (!$result || !is_array($result)) {
            return [];
        }

        $extracted = [];
        foreach ($document['fields'] as $field) {
            $key = $field['key'];
            if (isset($result[$key]) && is_string($result[$key]) && trim($result[$key]) !== '') {
                $extracted[$key] = trim($result[$key]);
            }
        }
        if (in_array($docType, self::RECIPIENT_REQUIRED_TYPES, true)
            && isset($result['recipient']) && is_string($result['recipient']) && trim($result['recipient']) !== '') {
            $extracted['recipient'] = trim($result['recipient']);
        }

        return $extracted;
    }

    private function generateDocument(string $docType, array $memory, string $lang): array
    {
        $document = self::DOCUMENTS[$docType];
        $dataJson = json_encode($memory, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $documentName = $document[$lang] ?? $document['ru'];
        $todayDate = now()->format('d.m.Y');

        $prompt = <<<PROMPT
Ты профессиональный специалист по официальным документам.
Создай документ на языке: {$lang}

Название: {$documentName}
Тип: {$docType}
Сегодняшняя дата: {$todayDate}

ТОЧНЫЕ ДАННЫЕ ПОЛЬЗОВАТЕЛЯ (используй ТОЛЬКО эти данные):
{$dataJson}

КРИТИЧЕСКИЕ ПРАВИЛА:
1. Используй ТОЛЬКО данные из блока выше.
2. НИКОГДА не придумывай ФИО, должность, организацию, сумму, дату, номер, адрес, паспорт.
3. НЕ МЕНЯЙ названия организаций — используй ТОЧНО как в данных (например "Алиф", не "Алӣ").
4. Не добавляй законы, статьи, нормативные акты, эмоциональные фразы.
5. Официальный деловой стиль.

ПРАВИЛА ШАПКИ (header):
- Строка 1 — АДРЕСАТ:
  * Если есть recipient и organization: объедини их грамматически правильно для языка {$lang} (пример ru: "Директору ООО Ромашка", пример tj: "Ба директори ҶДММ Ромашка").
  * Если только recipient: "Кому: <recipient>" / "Ба: <recipient>".
  * Если только organization: "Кому: <organization>" / "Ба: <organization>".
- Строка 2 — ОТПРАВИТЕЛЬ: "От: <имя>" / "Аз: <имя>".

ПРАВИЛА ТЕЛА (body):
- Документ должен быть ПОЛНЫМ и развёрнутым: 2-4 абзаца.
- Абзац 1: суть просьбы с должностью (используй position грамматически правильно для языка, склоняй если нужно).
- Абзац 2: детали — даты, сроки, суммы, причины (если есть в данных).
- Последний абзац: вежливая заключительная формула.
- Если данных нет — не упоминай их, НЕ ставь "____" в теле.

ПРАВИЛА ПОДПИСИ (signature):
- Строка 1: "____________ /<имя отправителя>/"
- Строка 2: сегодняшняя дата {$todayDate}

Верни СТРОГО JSON:
{
    "title": "название документа",
    "header": ["Кому/Ба: ...", "От/Аз: ..."],
    "body": ["абзац 1...", "абзац 2..."],
    "signature": ["____________ /Имя/", "{$todayDate}"]
}
PROMPT;

        $result = $this->askAI($prompt, 1800);

        $structure = null;

        if ($result && is_array($result)) {
            $header = $this->arrayValue($result['header'] ?? []);
            $title = (string) ($result['title'] ?? $documentName);
            $body = $this->arrayValue($result['body'] ?? []);
            $signature = $this->arrayValue($result['signature'] ?? []);

            if (!empty($title) || !empty($header) || !empty($body)) {
                $structure = [
                    'header' => $header,
                    'title' => $title,
                    'body' => $body,
                    'signature' => $signature,
                ];
            }
        }

        if (!$structure) {
            Log::info('AI returned empty structure, using fallback', ['doc_type' => $docType]);
            $structure = $this->fallbackDocument($docType, $memory, $lang);
        }

        return [
            'action' => 'document',
            'reply' => $lang === 'tj' ? '✅ Ҳуҷҷат тайёр аст! Шумо метавонед онро зеркашӣ кунед.' : '✅ Документ готов! Вы можете его скачать.',
            'lang' => $lang,
            'doc_type' => $docType,
            'memory' => $memory,
            'last_questioned_field' => null,
            'structure' => $structure,
            'pending_type_clarification' => null,
        ];
    }

    private function fallbackDocument(string $docType, array $memory, string $lang): array
    {
        $document = self::DOCUMENTS[$docType];
        $documentName = $document[$lang] ?? $document['ru'];
        $todayDate = now()->format('d.m.Y');

        $name = $memory['employee_name'] ?? $memory['applicant'] ?? $memory['person']
            ?? $memory['principal_name'] ?? $memory['seller'] ?? $memory['lender']
            ?? $memory['receiver'] ?? '____________';

        $organization = $memory['organization'] ?? $memory['institution'] ?? '';
        $recipient = $memory['recipient'] ?? '';

        if ($recipient !== '' && $organization !== '') {
            $headerTo = $recipient . ' ' . $organization;
        } elseif ($recipient !== '') {
            $headerTo = $recipient;
        } elseif ($organization !== '') {
            $headerTo = $organization;
        } else {
            $headerTo = '____________';
        }

        $position = $memory['position'] ?? $memory['current_position'] ?? '';

        if ($lang === 'tj') {
            $body = [];
            $body[] = "Ман, {$name}, " . ($position ? "дар вазифаи {$position} коркунанда, " : "") . "бо ҳамин ариза хоҳиш мекунам, ки масъалаи зеринро баррасӣ намоед:";
            if (isset($memory['reason'])) $body[] = "Сабаб: {$memory['reason']}";
            if (isset($memory['start_date'])) $body[] = "Санаи оғоз: {$memory['start_date']}";
            if (isset($memory['days'])) $body[] = "Шумораи рӯзҳо: {$memory['days']}";
            if (isset($memory['amount'])) $body[] = "Маблағ: {$memory['amount']}";
            if (isset($memory['request'])) $body[] = "Хоҳиш: {$memory['request']}";
            $body[] = "";
            $body[] = "Хоҳишмандам, аризаи маро қабул намуда, баррасӣ кунед.";

            return [
                'header' => ["Ба: {$headerTo}", "Аз: {$name}"],
                'title' => $documentName,
                'body' => $body,
                'signature' => ['____________ /' . $name . '/', $todayDate],
            ];
        }

        $body = [];
        $body[] = "Я, {$name}, " . ($position ? "работающий в должности {$position}, " : "") . "настоящим заявлением прошу рассмотреть следующий вопрос:";
        if (isset($memory['reason'])) $body[] = "Причина: {$memory['reason']}";
        if (isset($memory['start_date'])) $body[] = "Дата начала: {$memory['start_date']}";
        if (isset($memory['days'])) $body[] = "Количество дней: {$memory['days']}";
        if (isset($memory['amount'])) $body[] = "Сумма: {$memory['amount']}";
        if (isset($memory['request'])) $body[] = "Просьба: {$memory['request']}";
        $body[] = "";
        $body[] = "Прошу принять и рассмотреть моё заявление.";

        return [
            'header' => ["Кому: {$headerTo}", "От: {$name}"],
            'title' => $documentName,
            'body' => $body,
            'signature' => ['____________ /' . $name . '/', $todayDate],
        ];
    }

    private function askAI(string $prompt, int $maxTokens = 1000): ?array
    {
        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::timeout(60)
                ->connectTimeout(10)
                ->withToken($apiKey)
                ->acceptJson()
                ->post($this->url, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Ты работаешь только по заданию. Всегда возвращай корректный JSON. Никогда не выдумывай факты. Понимаешь русский и таджикский языки.',
                        ],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => $maxTokens,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if (!$response->successful()) {
                Log::error('AI HTTP error', ['status' => $response->status()]);
                return null;
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            return is_string($content) ? $this->parseJson($content) : null;

        } catch (\Throwable $e) {
            Log::error('AI request exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function getApiKey(): string
    {
        return trim((string) (
            config('services.ai.api_key')
            ?: config('services.groq.key')
            ?: env('GROQ_API_KEY', '')
        ));
    }

    private function parseJson(?string $content): ?array
    {
        if (!$content) return null;

        $content = trim($content);
        $content = preg_replace('/^```(?:json)?/i', '', $content);
        $content = preg_replace('/```$/', '', trim($content));

        $decoded = json_decode($content, true);
        if (is_array($decoded)) return $decoded;

        $start = strpos($content, '{');
        $end = strrpos($content, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $json = substr($content, $start, $end - $start + 1);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) return $decoded;
        }

        return null;
    }

    private function typeQuestion(string $lang): string
    {
        if ($lang === 'tj') {
            return "Кадом ҳуҷҷат ба шумо лозим аст? Масалан:\n- ариза барои кор (қабул ба кор)\n- рухсатӣ (отпуск)\n- ваколатнома (доверенность)\n- шартнома (договор)\n- маълумотнома (справка)\n- баёнотнома (объяснительная)";
        }
        return "Какой документ вам нужен? Например:\n- заявление на работу\n- отпуск\n- доверенность\n- договор\n- справка\n- объяснительная";
    }

    private function ask(string $lang, string $question): array
    {
        return [
            'action' => 'ask',
            'reply' => $question,
            'lang' => $lang,
            'doc_type' => null,
            'memory' => [],
            'last_questioned_field' => null,
            'structure' => null,
            'pending_type_clarification' => null,
        ];
    }

    private function arrayValue(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_map(fn($item) => (string) $item, $value));
        }
        if (is_string($value) && trim($value) !== '') {
            return [$value];
        }
        return [];
    }

    private function joinLines(array $lines): string
    {
        return implode("\n", $lines);
    }
}