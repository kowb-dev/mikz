// Поисковые комбинации для категорий товаров мобильных запчастей

const searchCombinations = {
  // ========== БРЕНДЫ ==========
  brands: {
    apple: [
      'apple', 'аппле', 'апл', 'эпл', 'эппл', 'апель',
      'фззду' // неверная раскладка
    ],
    iphone: [
      'iphone', 'айфон', 'ифон', 'айфон', 'іфон', 'iфон', 'айфoн',
      'b`jyt', 'b`jy' // неверная раскладка
    ],
    ipad: [
      'ipad', 'айпад', 'айпэд', 'ипад', 'іпад',
      'b`fl' // неверная раскладка
    ],
    samsung: [
      'samsung', 'самсунг', 'самсунк', 'сансунг', 'самсун', 'самс',
      'cfvceyu', 'cfvcey' // неверная раскладка
    ],
    xiaomi: [
      'xiaomi', 'сяоми', 'ксяоми', 'шаоми', 'сяоми', 'ксиаоми', 'сяоми',
      '[bfjvb', '[bfv' // неверная раскладка
    ],
    redmi: [
      'redmi', 'редми', 'редми', 'редми',
      'htlvb' // неверная раскладка
    ],
    huawei: [
      'huawei', 'хуавей', 'хуавэй', 'хуавэй', 'хавей', 'хуавай',
      'uefdtb', 'uefdt' // неверная раскладка
    ],
    honor: [
      'honor', 'хонор', 'хонор', 'хоннор',
      'ujyjh' // неверная раскладка
    ],
    nokia: [
      'nokia', 'нокиа', 'нокия', 'нокиа',
      'yjrbf' // неверная раскладка
    ],
    oppo: [
      'oppo', 'оппо', 'опо', 'оппо',
      'jggj' // неверная раскладка
    ],
    vivo: [
      'vivo', 'виво', 'віво', 'виво',
      'dbdj' // неверная раскладка
    ],
    realme: [
      'realme', 'реалме', 'риалми', 'риалме', 'реалми',
      'htfkvt' // неверная раскладка
    ],
    infinix: [
      'infinix', 'инфиникс', 'инфиникс', 'инфініх',
      'byabyb[' // неверная раскладка
    ],
    tecno: [
      'tecno', 'текно', 'тэкно', 'тэкно',
      'ntryj' // неверная раскладка
    ]
  },

  // ========== ТИПЫ ЗАПЧАСТЕЙ ==========
  partTypes: {
    display: [
      'дисплей', 'дисплей', 'диспей', 'дисплэй', 'диспл', 'дисп',
      'lbcgktq', 'lbcgk', // неверная раскладка
      'экран', 'экран', 'єкран',
      '\'rhfy', // неверная раскладка
      'lcd', 'лсд', 'лцд',
      'ktl', // неверная раскладка
      'модуль', 'модуль',
      'vjlekm' // неверная раскладка
    ],
    
    battery: [
      'акб', 'акб', 'акб',
      'fr,', // неверная раскладка
      'аккумулятор', 'аккум', 'батарея', 'батарейка', 'акум',
      'frrevekznjh', 'frreve', ',fnfhtz' // неверная раскладка
    ],
    
    backCover: [
      'задняя крышка', 'крышка', 'задняя', 'зад крышка',
      'pflyzz rhsirn', 'rhsirn', 'pflyzz', // неверная раскладка
      'корпус', 'корпус', 'корп',
      'rjhgec', 'rjhg', // неверная раскладка
      'рамка', 'рамка', 'рама',
      'hfvrf', 'hfvf' // неверная раскладка
    ],
    
    flex: [
      'шлейф', 'шлейф', 'шлеф', 'шлейф',
      'iktqa', 'ikta', // неверная раскладка
      'межплатный', 'межплатный', 'межплат',
      'vt;gkfnysq', 'vt;gkfn', // неверная раскладка
      'флекс', 'флекс',
      'aktrc' // неверная раскладка
    ],
    
    chargingPort: [
      'шлейф зарядки', 'зарядка', 'порт зарядки',
      'iktqa pfhzlrb', 'pfhzlrf', // неверная раскладка
      'плата зарядки', 'charging port',
      'gkfnf pfhzlrb', // неверная раскладка
      'разъем', 'разъем', 'разьем',
      'hfp]tv' // неверная раскладка
    ],
    
    glass: [
      'стекло', 'стекло', 'стекл',
      'cntrkj', 'cntrkj', // неверная раскладка
      'тачскрин', 'тачскрін', 'тачскрин', 'тач',
      'nfxcrhby', 'nfx', // неверная раскладка
      'переклейка', 'переклейка',
      'gthtrktrrf' // неверная раскладка
    ],
    
    speaker: [
      'динамик', 'динамик', 'динамік', 'дин',
      'lbyfvbr', 'lby', // неверная раскладка
      'динамики', 'спикер', 'speaker',
      'lbyfvbrb', 'cgbrhh' // неверная раскладка
    ],
    
    accessories: [
      'аксессуары', 'аксесуары', 'аксесуари', 'аксес',
      'frcttcefhs', 'frcttc', // неверная раскладка
      'аксессуар', 'прочее', 'прочие',
      'ghjxtt', 'ghjx' // неверная раскладка
    ],
    
    charger: [
      'сзу', 'зарядное', 'зарядное устройство', 'зарядка',
      'cpe', 'pfhzlyjt', 'pfhzlrf', // неверная раскладка
      'адаптер', 'блок', 'блок питания',
      'flfgnth', ',kjr' // неверная раскладка
    ]
  },

  // ========== СОСТАВНЫЕ КОМБИНАЦИИ ==========
  // Примеры комбинированных запросов
  commonCombinations: [
    // iPhone
    'дисплей айфон', 'lbcgktq b`jy', 'экран iphone', '\'rhfy b`jyt',
    'акб айфон', 'fr, b`jy', 'батарея iphone', ',fnfhtz b`jyt',
    'крышка айфон', 'rhsirn b`jy', 'корпус iphone', 'rjhgec b`jyt',
    'шлейф айфон', 'iktqa b`jy', 'зарядка iphone', 'pfhzlrf b`jyt',
    'стекло айфон', 'cntrkj b`jy', 'тачскрин iphone', 'nfxcrhby b`jyt',
    
    // Samsung
    'дисплей самсунг', 'lbcgktq cfvceyu', 'экран samsung', '\'rhfy cfvceyu',
    'акб самсунг', 'fr, cfvceyu', 'батарея samsung', ',fnfhtz cfvceyu',
    'крышка самсунг', 'rhsirn cfvceyu', 'корпус samsung', 'rjhgec cfvceyu',
    'шлейф самсунг', 'iktqa cfvceyu', 'зарядка samsung', 'pfhzlrf cfvceyu',
    
    // Xiaomi/Redmi
    'дисплей сяоми', 'lbcgktq [bfjvb', 'экран xiaomi', '\'rhfy [bfjvb',
    'акб сяоми', 'fr, [bfjvb', 'батарея xiaomi', ',fnfhtz [bfjvb',
    'дисплей редми', 'lbcgktq htlvb', 'экран redmi', '\'rhfy htlvb',
    'акб редми', 'fr, htlvb', 'батарея redmi', ',fnfhtz htlvb',
    
    // Huawei/Honor
    'дисплей хуавей', 'lbcgktq uefdtb', 'экран huawei', '\'rhfy uefdtb',
    'акб хуавей', 'fr, uefdtb', 'батарея huawei', ',fnfhtz uefdtb',
    'дисплей хонор', 'lbcgktq ujyjh', 'экран honor', '\'rhfy ujyjh',
    
    // Другие бренды
    'дисплей реалме', 'lbcgktq htfkvt', 'экран realme', '\'rhfy htfkvt',
    'дисплей инфиникс', 'lbcgktq byabyb[', 'экран infinix', '\'rhfy byabyb[',
    'дисплей текно', 'lbcgktq ntryj', 'экран tecno', '\'rhfy ntryj',
    'дисплей виво', 'lbcgktq dbdj', 'экран vivo', '\'rhfy dbdj',
    'дисплей оппо', 'lbcgktq jggj', 'экран oppo', '\'rhfy jggj',
    'дисплей нокиа', 'lbcgktq yjrbf', 'экран nokia', '\'rhfy yjrbf'
  ],

  // ========== МОДЕЛИ (ПРИМЕРЫ) ==========
  modelExamples: {
    iphone: [
      'iphone 11', 'айфон 11', 'b`jy 11', 'айфон одиннадцать',
      'iphone 12', 'айфон 12', 'b`jy 12', 'айфон двенадцать',
      'iphone 13', 'айфон 13', 'b`jy 13', 'айфон тринадцать',
      'iphone 14', 'айфон 14', 'b`jy 14', 'айфон четырнадцать',
      'iphone 15', 'айфон 15', 'b`jy 15', 'айфон пятнадцать',
      'iphone x', 'айфон х', 'b`jy [', 'айфон икс', 'айфон 10',
      'iphone xs', 'айфон xs', 'b`jy [c', 'айфон хс',
      'iphone xr', 'айфон xr', 'b`jy [h', 'айфон хр',
      'iphone se', 'айфон se', 'b`jy ct', 'айфон се'
    ],
    samsung: [
      'samsung a', 'самсунг а', 'cfvceyu f', 'samsung galaxy a',
      'samsung s', 'самсунг с', 'cfvceyu c', 'samsung galaxy s',
      'samsung m', 'самсунг м', 'cfvceyu v', 'samsung galaxy m',
      'samsung note', 'самсунг ноут', 'cfvceyu yjne', 'samsung galaxy note',
      's20', 's21', 's22', 's23', 's24',
      'a50', 'a51', 'a52', 'a53', 'a54',
      'a10', 'a20', 'a30', 'a40'
    ],
    xiaomi: [
      'redmi note', 'редми ноут', 'htlvb yjne', 'redmi note',
      'redmi 9', 'редми 9', 'htlvb 9',
      'redmi 10', 'редми 10', 'htlvb 10',
      'redmi 11', 'редми 11', 'htlvb 11',
      'redmi 12', 'редми 12', 'htlvb 12',
      'mi 9', 'ми 9', 'vb 9', 'mi9',
      'mi 10', 'ми 10', 'vb 10', 'mi10',
      'mi 11', 'ми 11', 'vb 11', 'mi11',
      'poco', 'поко', 'gjrj', 'poco x3', 'poco f3'
    ]
  },

  // ========== КАЧЕСТВО/ТИП ==========
  qualityTypes: [
    'оригинал', 'оригінал', 'orig', 'original',
    'jhbubyfk', 'jhbu', // неверная раскладка
    'копия', 'копія', 'copy', 'replica',
    'rjgbz', 'rjg', // неверная раскладка
    'oem', 'оем', 'оэм',
    'jtv', // неверная раскладка
    'aaa', 'ааа', 'высокое качество',
    'dscjrjt rfxtcndj', // неверная раскладка
    'incell', 'инсел', 'инселл',
    'byctkk', // неверная раскладка
    'tft', 'тфт',
    'nad', // неверная раскладка
    'amoled', 'амолед', 'амоled',
    'fvjktl', // неверная раскладка
    'oled', 'олед',
    'jktl', // неверная раскладка
    'ips', 'ипс',
    'bgc' // неверная раскладка
  ],

  // ========== ЦВЕТА ==========
  colors: [
    'черный', 'черн', 'black', 'xthysq', 'xthysq',
    'белый', 'бел', 'white', ',tksq', ',tkysq',
    'синий', 'син', 'blue', 'cbybq', 'cbybq',
    'красный', 'красн', 'red', 'rhfcysq', 'rhfcysq',
    'зеленый', 'зелен', 'green', 'ptktysq', 'ptktysq',
    'золотой', 'золот', 'gold', 'pjkjnjq', 'pjkjnjq',
    'серый', 'сер', 'gray', 'grey', 'cthsq', 'cthsq',
    'розовый', 'розов', 'pink', 'rose', 'hjpjdsq', 'hjpjdsq',
    'фиолетовый', 'фиолет', 'purple', 'абюктnjdsq', 'абюktnjdsq'
  ],

  // ========== ДОПОЛНИТЕЛЬНЫЕ ТЕРМИНЫ ==========
  additionalTerms: [
    'запчасти', 'запчастини', 'parts', 'pfgxfcnb',
    'ремонт', 'ремонт', 'repair', 'htvjyn',
    'замена', 'заміна', 'replacement', 'pfvtyf',
    'сервис', 'сервіс', 'service', 'cthдбc',
    'новый', 'новий', 'new', 'yjdsq',
    'бу', 'б/у', 'used', 'бувший в употреблении', ',e',
    'оптом', 'опт', 'wholesale', 'jgnjv',
    'розница', 'розніца', 'retail', 'hjpybwf'
  ]
};

// Функция для генерации всех возможных комбинаций
function generateSearchPatterns() {
  const patterns = [];
  
  // Добавляем все базовые варианты
  for (const category in searchCombinations) {
    if (typeof searchCombinations[category] === 'object' && !Array.isArray(searchCombinations[category])) {
      for (const subCategory in searchCombinations[category]) {
        patterns.push(...searchCombinations[category][subCategory]);
      }
    } else if (Array.isArray(searchCombinations[category])) {
      patterns.push(...searchCombinations[category]);
    }
  }
  
  return [...new Set(patterns)]; // Убираем дубликаты
}

// Экспорт для использования
if (typeof module !== 'undefined' && module.exports) {
  module.exports = searchCombinations;
}

console.log('Всего уникальных поисковых паттернов:', generateSearchPatterns().length);
console.log('\nПример использования:');
console.log('Бренд Apple:', searchCombinations.brands.apple);
console.log('Тип "Дисплей":', searchCombinations.partTypes.display);
console.log('Популярные комбинации:', searchCombinations.commonCombinations.slice(0, 5));