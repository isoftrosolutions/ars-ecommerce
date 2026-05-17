/**
 * Nepal Administrative Data
 * Complete dataset: 7 Provinces, 77 Districts, 753 Local Levels
 */
const NEPAL_ADDRESS = {
  provinces: [
    {
      id: 1,
      name: 'Koshi Pradesh',
      name_np: 'कोशी प्रदेश',
      districts: [
        {
          name: 'Taplejung',
          name_np: 'ताप्लेजुङ',
          municipalities: [
            { name: 'Phungling', type: 'Municipality', wards: 11 },
            { name: 'Aathrai Triveni', type: 'Rural Municipality', wards: 5 },
            { name: 'Sidingwa', type: 'Rural Municipality', wards: 5 },
            { name: 'Phaktanglung', type: 'Rural Municipality', wards: 5 },
            { name: 'Mikkwakhola', type: 'Rural Municipality', wards: 5 },
            { name: 'Meringden', type: 'Rural Municipality', wards: 5 },
            { name: 'Maiwakhola', type: 'Rural Municipality', wards: 5 },
            { name: 'Pathivara Yangwarak', type: 'Rural Municipality', wards: 5 },
            { name: 'Sirijangha', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Sankhuwasabha',
          name_np: 'सङ्खुवासभा',
          municipalities: [
            { name: 'Khandbari', type: 'Municipality', wards: 11 },
            { name: 'Chainpur', type: 'Municipality', wards: 11 },
            { name: 'Dharmadevi', type: 'Municipality', wards: 9 },
            { name: 'Panchkhapan', type: 'Municipality', wards: 9 },
            { name: 'Madi', type: 'Municipality', wards: 9 },
            { name: 'Sabhapokhari', type: 'Rural Municipality', wards: 5 },
            { name: 'Bhotkhola', type: 'Rural Municipality', wards: 5 },
            { name: 'Chichila', type: 'Rural Municipality', wards: 5 },
            { name: 'Makalu', type: 'Rural Municipality', wards: 5 },
            { name: 'Silichong', type: 'Rural Municipality', wards: 5 },
            { name: 'Sawakhola', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Solukhumbu',
          name_np: 'सोलुखुम्बु',
          municipalities: [
            { name: 'Dudhkunda', type: 'Municipality', wards: 13 },
            { name: 'Khadadevi', type: 'Rural Municipality', wards: 5 },
            { name: 'Sotang', type: 'Rural Municipality', wards: 5 },
            { name: 'Likhupike', type: 'Rural Municipality', wards: 5 },
            { name: 'Mahakulung', type: 'Rural Municipality', wards: 5 },
            { name: 'Nechasalyan', type: 'Rural Municipality', wards: 5 },
            { name: 'Dudhkoshi', type: 'Rural Municipality', wards: 5 },
            { name: 'Dhudhelu', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Okhaldhunga',
          name_np: 'ओखलढुङ्गा',
          municipalities: [
            { name: 'Siddhicharan', type: 'Municipality', wards: 11 },
            { name: 'Khijidemba', type: 'Rural Municipality', wards: 7 },
            { name: 'Champadevi', type: 'Rural Municipality', wards: 7 },
            { name: 'Chisankhugadhi', type: 'Rural Municipality', wards: 5 },
            { name: 'Molung', type: 'Rural Municipality', wards: 6 },
            { name: 'Likhu', type: 'Rural Municipality', wards: 6 },
            { name: 'Manebhanjyang', type: 'Rural Municipality', wards: 6 },
            { name: 'Sunkoshi', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Khotang',
          name_np: 'खोटाङ',
          municipalities: [
            { name: 'Halesi Tuwachung', type: 'Municipality', wards: 11 },
            { name: 'Rupakot Majhuwagadhi', type: 'Municipality', wards: 12 },
            { name: 'Diktel', type: 'Rural Municipality', wards: 9 },
            { name: 'Ainselukhark', type: 'Rural Municipality', wards: 5 },
            { name: 'Kepilasgadhi', type: 'Rural Municipality', wards: 5 },
            { name: 'Barahapokhari', type: 'Rural Municipality', wards: 5 },
            { name: 'Lamidanda', type: 'Rural Municipality', wards: 5 },
            { name: 'Sakela', type: 'Rural Municipality', wards: 5 },
            { name: 'Jantedhunga', type: 'Rural Municipality', wards: 5 },
            { name: 'Rawabesi', type: 'Rural Municipality', wards: 5 },
            { name: 'Diprung', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Bhojpur',
          name_np: 'भोजपुर',
          municipalities: [
            { name: 'Bhojpur', type: 'Municipality', wards: 11 },
            { name: 'Shadananda', type: 'Municipality', wards: 11 },
            { name: 'Hatuwagadhi', type: 'Rural Municipality', wards: 5 },
            { name: 'Ramprasad Rai', type: 'Rural Municipality', wards: 5 },
            { name: 'Aamchowk', type: 'Rural Municipality', wards: 5 },
            { name: 'Tyamke Maiyum', type: 'Rural Municipality', wards: 5 },
            { name: 'Arun', type: 'Rural Municipality', wards: 5 },
            { name: 'Pauwadungma', type: 'Rural Municipality', wards: 5 },
            { name: 'Salpa Silichho', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Dhankuta',
          name_np: 'धनकुटा',
          municipalities: [
            { name: 'Dhankuta', type: 'Municipality', wards: 11 },
            { name: 'Pakhribas', type: 'Municipality', wards: 9 },
            { name: 'Mahalaxmi', type: 'Municipality', wards: 9 },
            { name: 'Sangurigadhi', type: 'Rural Municipality', wards: 5 },
            { name: 'Chaubise', type: 'Rural Municipality', wards: 5 },
            { name: 'Sahidbhumi', type: 'Rural Municipality', wards: 5 },
            { name: 'Chhathar Jorpati', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Tehrathum',
          name_np: 'तेह्रथुम',
          municipalities: [
            { name: 'Myanglung', type: 'Municipality', wards: 9 },
            { name: 'Laligurans', type: 'Municipality', wards: 9 },
            { name: 'Aathrai', type: 'Rural Municipality', wards: 5 },
            { name: 'Phedap', type: 'Rural Municipality', wards: 5 },
            { name: 'Chhathar', type: 'Rural Municipality', wards: 5 },
            { name: 'Menchhayayem', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Panchthar',
          name_np: 'पाँचथर',
          municipalities: [
            { name: 'Phidim', type: 'Municipality', wards: 11 },
            { name: 'Kummayak', type: 'Rural Municipality', wards: 5 },
            { name: 'Miklajung', type: 'Rural Municipality', wards: 5 },
            { name: 'Tumbewa', type: 'Rural Municipality', wards: 5 },
            { name: 'Falelung', type: 'Rural Municipality', wards: 5 },
            { name: 'Phalgunanda', type: 'Rural Municipality', wards: 5 },
            { name: 'Hilihang', type: 'Rural Municipality', wards: 5 },
            { name: 'Yangwarak', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Ilam',
          name_np: 'इलाम',
          municipalities: [
            { name: 'Ilam', type: 'Municipality', wards: 10 },
            { name: 'Deumai', type: 'Municipality', wards: 9 },
            { name: 'Mai', type: 'Municipality', wards: 10 },
            { name: 'Suryodaya', type: 'Municipality', wards: 10 },
            { name: 'Phakphokthum', type: 'Rural Municipality', wards: 5 },
            { name: 'Mai Jogmai', type: 'Rural Municipality', wards: 5 },
            { name: 'Chulachuli', type: 'Rural Municipality', wards: 5 },
            { name: 'Rong', type: 'Rural Municipality', wards: 5 },
            { name: 'Mangchhebung', type: 'Rural Municipality', wards: 5 },
            { name: 'Sandakpur', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Jhapa',
          name_np: 'झापा',
          municipalities: [
            { name: 'Mechinagar', type: 'Municipality', wards: 15 },
            { name: 'Bhadrapur', type: 'Municipality', wards: 10 },
            { name: 'Birtamod', type: 'Municipality', wards: 10 },
            { name: 'Damak', type: 'Municipality', wards: 13 },
            { name: 'Kankai', type: 'Municipality', wards: 9 },
            { name: 'Shivasatakshi', type: 'Municipality', wards: 11 },
            { name: 'Gauradaha', type: 'Municipality', wards: 9 },
            { name: 'Buddhashanti', type: 'Rural Municipality', wards: 7 },
            { name: 'Haldibari', type: 'Rural Municipality', wards: 5 },
            { name: 'Kachankawal', type: 'Rural Municipality', wards: 7 },
            { name: 'Jhapa', type: 'Rural Municipality', wards: 7 },
            { name: 'Barhadashi', type: 'Rural Municipality', wards: 7 },
            { name: 'Gaurigunj', type: 'Rural Municipality', wards: 6 },
            { name: 'Kamal', type: 'Rural Municipality', wards: 7 }
          ]
        },
        {
          name: 'Morang',
          name_np: 'मोरङ',
          municipalities: [
            { name: 'Biratnagar', type: 'Metropolitan City', wards: 19 },
            { name: 'Belbari', type: 'Municipality', wards: 11 },
            { name: 'Letang', type: 'Municipality', wards: 9 },
            { name: 'Rangeli', type: 'Municipality', wards: 9 },
            { name: 'Ratuwamai', type: 'Municipality', wards: 10 },
            { name: 'Pathari Sanischare', type: 'Municipality', wards: 10 },
            { name: 'Urlabari', type: 'Municipality', wards: 9 },
            { name: 'Sundar Haraicha', type: 'Municipality', wards: 12 },
            { name: 'Miklajung', type: 'Rural Municipality', wards: 5 },
            { name: 'Jahada', type: 'Rural Municipality', wards: 6 },
            { name: 'Dhanpalthan', type: 'Rural Municipality', wards: 5 },
            { name: 'Gramthan', type: 'Rural Municipality', wards: 5 },
            { name: 'Budhiganga', type: 'Rural Municipality', wards: 7 },
            { name: 'Katahari', type: 'Rural Municipality', wards: 6 },
            { name: 'Koshi', type: 'Rural Municipality', wards: 5 },
            { name: 'Sunwarshi', type: 'Rural Municipality', wards: 5 },
            { name: 'Kerabari', type: 'Rural Municipality', wards: 7 }
          ]
        },
        {
          name: 'Sunsari',
          name_np: 'सुनसरी',
          municipalities: [
            { name: 'Ithari', type: 'Sub-Metropolitan City', wards: 20 },
            { name: 'Dharan', type: 'Sub-Metropolitan City', wards: 20 },
            { name: 'Inaruwa', type: 'Municipality', wards: 10 },
            { name: 'Duhabi', type: 'Municipality', wards: 12 },
            { name: 'Ramdhuni', type: 'Municipality', wards: 10 },
            { name: 'Barahchhetra', type: 'Municipality', wards: 9 },
            { name: 'Dewanganj', type: 'Rural Municipality', wards: 5 },
            { name: 'Gadhi', type: 'Rural Municipality', wards: 5 },
            { name: 'Barju', type: 'Rural Municipality', wards: 5 },
            { name: 'Koshi', type: 'Rural Municipality', wards: 5 },
            { name: 'Harinagara', type: 'Rural Municipality', wards: 7 },
            { name: 'Bhokraha', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Udayapur',
          name_np: 'उदयपुर',
          municipalities: [
            { name: 'Gai ghat', type: 'Municipality', wards: 12 },
            { name: 'Katari', type: 'Municipality', wards: 11 },
            { name: 'Chaudandigadhi', type: 'Municipality', wards: 10 },
            { name: 'Triyuga', type: 'Municipality', wards: 14 },
            { name: 'Belaka', type: 'Municipality', wards: 9 },
            { name: 'Rautamai', type: 'Rural Municipality', wards: 6 },
            { name: 'Tapli', type: 'Rural Municipality', wards: 5 },
            { name: 'Limchungbung', type: 'Rural Municipality', wards: 5 }
          ]
        }
      ]
    },
    {
      id: 2,
      name: 'Madhesh Pradesh',
      name_np: 'मधेश प्रदेश',
      districts: [
        {
          name: 'Saptari',
          name_np: 'सप्तरी',
          municipalities: [
            { name: 'Rajbiraj', type: 'Municipality', wards: 14 },
            { name: 'Bhadrapur', type: 'Municipality', wards: 9 },
            { name: 'Shambhunath', type: 'Municipality', wards: 12 },
            { name: 'Kanchanrup', type: 'Municipality', wards: 10 },
            { name: 'Dakneshwori', type: 'Municipality', wards: 10 },
            { name: 'Bode Barasain', type: 'Municipality', wards: 9 },
            { name: 'Khadak', type: 'Municipality', wards: 9 },
            { name: 'Hanumannagar Kankalini', type: 'Municipality', wards: 11 },
            { name: 'Tilathi Koiladi', type: 'Rural Municipality', wards: 7 },
            { name: 'Rupani', type: 'Rural Municipality', wards: 6 },
            { name: 'Surunga', type: 'Rural Municipality', wards: 7 },
            { name: 'Bishnupur', type: 'Rural Municipality', wards: 5 },
            { name: 'Balan Bihul', type: 'Rural Municipality', wards: 6 },
            { name: 'Belhi Chapena', type: 'Rural Municipality', wards: 5 },
            { name: 'Mahadeva', type: 'Rural Municipality', wards: 6 },
            { name: 'Saptakoshi', type: 'Rural Municipality', wards: 7 },
            { name: 'Rupatol Bahagwati', type: 'Rural Municipality', wards: 5 },
            { name: 'Agnisair Krishnasavaran', type: 'Rural Municipality', wards: 6 },
            { name: 'Chhinnamasta', type: 'Rural Municipality', wards: 7 }
          ]
        },
        {
          name: 'Siraha',
          name_np: 'सिराहा',
          municipalities: [
            { name: 'Lahan', type: 'Municipality', wards: 14 },
            { name: 'Siraha', type: 'Municipality', wards: 14 },
            { name: 'Mirchaiya', type: 'Municipality', wards: 11 },
            { name: 'Kalyanpur', type: 'Municipality', wards: 9 },
            { name: 'Golbazar', type: 'Municipality', wards: 11 },
            { name: 'Dhangadhimai', type: 'Municipality', wards: 10 },
            { name: 'Nawarajpur', type: 'Rural Municipality', wards: 5 },
            { name: 'Bariyarpatti', type: 'Rural Municipality', wards: 6 },
            { name: 'Aurahi', type: 'Rural Municipality', wards: 5 },
            { name: 'Sakhuwanankarkatti', type: 'Rural Municipality', wards: 5 },
            { name: 'Bhagawanpur', type: 'Rural Municipality', wards: 5 },
            { name: 'Naraha', type: 'Rural Municipality', wards: 5 },
            { name: 'Bishnupur', type: 'Rural Municipality', wards: 5 },
            { name: 'Karahiyahi', type: 'Rural Municipality', wards: 5 },
            { name: 'Sukhipur', type: 'Municipality', wards: 10 },
            { name: 'Laxmipur Patari', type: 'Rural Municipality', wards: 6 },
            { name: 'Arnama', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Dhanusa',
          name_np: 'धनुषा',
          municipalities: [
            { name: 'Janakpur', type: 'Sub-Metropolitan City', wards: 25 },
            { name: 'Chhireshwornath', type: 'Municipality', wards: 10 },
            { name: 'Ganeshman Charnath', type: 'Municipality', wards: 11 },
            { name: 'Dhanushadham', type: 'Municipality', wards: 9 },
            { name: 'Nagarain', type: 'Municipality', wards: 9 },
            { name: 'Bideha', type: 'Municipality', wards: 9 },
            { name: 'Mithila Bihari', type: 'Municipality', wards: 9 },
            { name: 'Hansapur', type: 'Municipality', wards: 9 },
            { name: 'Sabaila', type: 'Municipality', wards: 12 },
            { name: 'Kamala', type: 'Municipality', wards: 9 },
            { name: 'Mithila', type: 'Municipality', wards: 11 },
            { name: 'Laxminiya', type: 'Rural Municipality', wards: 7 },
            { name: 'Aurahi', type: 'Rural Municipality', wards: 5 },
            { name: 'Dhaanouji', type: 'Rural Municipality', wards: 5 },
            { name: 'Nawakothari', type: 'Rural Municipality', wards: 5 },
            { name: 'Bateshwar', type: 'Rural Municipality', wards: 5 },
            { name: 'Mukhiyapatti Musarmiya', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Mahottari',
          name_np: 'महोत्तरी',
          municipalities: [
            { name: 'Jaleshwor', type: 'Municipality', wards: 12 },
            { name: 'Bardibas', type: 'Municipality', wards: 14 },
            { name: 'Gaushala', type: 'Municipality', wards: 11 },
            { name: 'Loharpatti', type: 'Municipality', wards: 9 },
            { name: 'Ramgopalpur', type: 'Municipality', wards: 9 },
            { name: 'Aurahi', type: 'Municipality', wards: 9 },
            { name: 'Manara Shiswa', type: 'Municipality', wards: 10 },
            { name: 'Matihani', type: 'Municipality', wards: 9 },
            { name: 'Bhangaha', type: 'Municipality', wards: 9 },
            { name: 'Balawa', type: 'Municipality', wards: 11 },
            { name: 'Samsi', type: 'Rural Municipality', wards: 5 },
            { name: 'Ekadara', type: 'Rural Municipality', wards: 5 },
            { name: 'Sonama', type: 'Rural Municipality', wards: 5 },
            { name: 'Mahottari', type: 'Rural Municipality', wards: 5 },
            { name: 'Pipra', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Sarlahi',
          name_np: 'सर्लाही',
          municipalities: [
            { name: 'Malangwa', type: 'Municipality', wards: 12 },
            { name: 'Harivan', type: 'Municipality', wards: 9 },
            { name: 'Lalbandi', type: 'Municipality', wards: 13 },
            { name: 'Ishworpur', type: 'Municipality', wards: 11 },
            { name: 'Barahathawa', type: 'Municipality', wards: 11 },
            { name: 'Balara', type: 'Municipality', wards: 11 },
            { name: 'Godaita', type: 'Municipality', wards: 9 },
            { name: 'Bagmati', type: 'Municipality', wards: 9 },
            { name: 'Chakraghatta', type: 'Rural Municipality', wards: 5 },
            { name: 'Basbariya', type: 'Rural Municipality', wards: 5 },
            { name: 'Bishnu', type: 'Rural Municipality', wards: 5 },
            { name: 'Bramhapuri', type: 'Rural Municipality', wards: 5 },
            { name: 'Chandranagar', type: 'Rural Municipality', wards: 5 },
            { name: 'Dhankaul', type: 'Rural Municipality', wards: 5 },
            { name: 'Kabi Lal', type: 'Rural Municipality', wards: 5 },
            { name: 'Haripurwa', type: 'Rural Municipality', wards: 5 },
            { name: 'Parsa', type: 'Rural Municipality', wards: 5 },
            { name: 'Ramnagar', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Rautahat',
          name_np: 'रौतहट',
          municipalities: [
            { name: 'Gaur', type: 'Municipality', wards: 11 },
            { name: 'Baudhimai', type: 'Municipality', wards: 9 },
            { name: 'Brindaban', type: 'Municipality', wards: 9 },
            { name: 'Chandranigapur', type: 'Municipality', wards: 9 },
            { name: 'Dewahi Gonahi', type: 'Municipality', wards: 9 },
            { name: 'Gadhimai', type: 'Municipality', wards: 9 },
            { name: 'Garuda', type: 'Municipality', wards: 9 },
            { name: 'Gujara', type: 'Municipality', wards: 9 },
            { name: 'Ishanath', type: 'Municipality', wards: 9 },
            { name: 'Katahariya', type: 'Municipality', wards: 9 },
            { name: 'Madhav Narayan', type: 'Municipality', wards: 9 },
            { name: 'Maulapur', type: 'Municipality', wards: 9 },
            { name: 'Phatuwa Bijayapur', type: 'Municipality', wards: 9 },
            { name: 'Rajdevi', type: 'Municipality', wards: 9 },
            { name: 'Rajpur', type: 'Municipality', wards: 9 },
            { name: 'Yamunamai', type: 'Rural Municipality', wards: 5 },
            { name: 'Durga Bhagawati', type: 'Rural Municipality', wards: 5 },
            { name: 'Parchaiwar', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Bara',
          name_np: 'बारा',
          municipalities: [
            { name: 'Kalaiya', type: 'Sub-Metropolitan City', wards: 23 },
            { name: 'Jeetpur Simara', type: 'Sub-Metropolitan City', wards: 24 },
            { name: 'Kolhabi', type: 'Municipality', wards: 9 },
            { name: 'Nijgadh', type: 'Municipality', wards: 11 },
            { name: 'Mahagadhimai', type: 'Municipality', wards: 11 },
            { name: 'Simraungadh', type: 'Municipality', wards: 9 },
            { name: 'Bishrampur', type: 'Rural Municipality', wards: 5 },
            { name: 'Pheta', type: 'Rural Municipality', wards: 5 },
            { name: 'Prasauni', type: 'Rural Municipality', wards: 5 },
            { name: 'Adarsh Kotwal', type: 'Rural Municipality', wards: 5 },
            { name: 'Suwarna', type: 'Rural Municipality', wards: 5 },
            { name: 'Baragadhi', type: 'Rural Municipality', wards: 5 },
            { name: 'Parawanipur', type: 'Rural Municipality', wards: 5 },
            { name: 'Karaiyamai', type: 'Rural Municipality', wards: 6 },
            { name: 'Devtal', type: 'Rural Municipality', wards: 5 },
            { name: 'Pacharauta', type: 'Rural Municipality', wards: 5 },
            { name: 'Kadamdanda', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Parsa',
          name_np: 'पर्सा',
          municipalities: [
            { name: 'Birgunj', type: 'Metropolitan City', wards: 32 },
            { name: 'Bahudaramai', type: 'Municipality', wards: 9 },
            { name: 'Parsagadhi', type: 'Municipality', wards: 9 },
            { name: 'Pakahawa', type: 'Municipality', wards: 9 },
            { name: 'Pokhariya', type: 'Municipality', wards: 9 },
            { name: 'Jirabhawani', type: 'Municipality', wards: 9 },
            { name: 'Kalikamai', type: 'Rural Municipality', wards: 5 },
            { name: 'Sakhuwa Parsauni', type: 'Rural Municipality', wards: 5 },
            { name: 'Bindabasini', type: 'Rural Municipality', wards: 5 },
            { name: 'Chhipaharmai', type: 'Rural Municipality', wards: 5 },
            { name: 'Jagarnathpur', type: 'Rural Municipality', wards: 5 },
            { name: 'Dharmapur', type: 'Rural Municipality', wards: 5 },
            { name: 'Thori', type: 'Rural Municipality', wards: 5 },
            { name: 'Paterwa Sugauli', type: 'Rural Municipality', wards: 5 }
          ]
        }
      ]
    },
    {
      id: 3,
      name: 'Bagmati Pradesh',
      name_np: 'बागमती प्रदेश',
      districts: [
        {
          name: 'Sindhuli',
          name_np: 'सिन्धुली',
          municipalities: [
            { name: 'Kamalamai', type: 'Municipality', wards: 13 },
            { name: 'Dudhouli', type: 'Municipality', wards: 9 },
            { name: 'Sunkoshi', type: 'Rural Municipality', wards: 7 },
            { name: 'Hariharpur', type: 'Rural Municipality', wards: 5 },
            { name: 'Marin', type: 'Rural Municipality', wards: 5 },
            { name: 'Tinpatan', type: 'Rural Municipality', wards: 5 },
            { name: 'Ghyanglekh', type: 'Rural Municipality', wards: 5 },
            { name: 'Fikkal', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Makwanpur',
          name_np: 'मकवानपुर',
          municipalities: [
            { name: 'Hetauda', type: 'Sub-Metropolitan City', wards: 19 },
            { name: 'Thaha', type: 'Municipality', wards: 11 },
            { name: 'Bhimphedi', type: 'Rural Municipality', wards: 5 },
            { name: 'Makwanpurgadhi', type: 'Rural Municipality', wards: 5 },
            { name: 'Manahari', type: 'Rural Municipality', wards: 5 },
            { name: 'Raksirang', type: 'Rural Municipality', wards: 5 },
            { name: 'Kailash', type: 'Rural Municipality', wards: 5 },
            { name: 'Bakaiya', type: 'Rural Municipality', wards: 5 },
            { name: 'Bagmati', type: 'Rural Municipality', wards: 6 },
            { name: 'Indrasarowar', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Ramechhap',
          name_np: 'रामेछाप',
          municipalities: [
            { name: 'Manthali', type: 'Municipality', wards: 11 },
            { name: 'Ramechhap', type: 'Municipality', wards: 9 },
            { name: 'Umakunda', type: 'Rural Municipality', wards: 5 },
            { name: 'Khandadevi', type: 'Rural Municipality', wards: 5 },
            { name: 'Gokulganga', type: 'Rural Municipality', wards: 5 },
            { name: 'Likhu', type: 'Rural Municipality', wards: 5 },
            { name: 'Sunapati', type: 'Rural Municipality', wards: 5 },
            { name: 'Doramba', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Dolakha',
          name_np: 'दोलखा',
          municipalities: [
            { name: 'Bhimeshwor', type: 'Municipality', wards: 11 },
            { name: 'Jiri', type: 'Municipality', wards: 9 },
            { name: 'Kalinchok', type: 'Rural Municipality', wards: 5 },
            { name: 'Melung', type: 'Rural Municipality', wards: 5 },
            { name: 'Bigu', type: 'Rural Municipality', wards: 5 },
            { name: 'Gaurishankar', type: 'Rural Municipality', wards: 5 },
            { name: 'Baiteshwar', type: 'Rural Municipality', wards: 5 },
            { name: 'Shailung', type: 'Rural Municipality', wards: 5 },
            { name: 'Tamakoshi', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Bhaktapur',
          name_np: 'भक्तपुर',
          municipalities: [
            { name: 'Bhaktapur', type: 'Municipality', wards: 17 },
            { name: 'Changunarayan', type: 'Municipality', wards: 9 },
            { name: 'Madhyapur Thimi', type: 'Municipality', wards: 9 },
            { name: 'Suryabinayak', type: 'Municipality', wards: 10 }
          ]
        },
        {
          name: 'Dhading',
          name_np: 'धादिङ',
          municipalities: [
            { name: 'Dhading Besi', type: 'Municipality', wards: 11 },
            { name: 'Nilkantha', type: 'Municipality', wards: 14 },
            { name: 'Khaniyabas', type: 'Rural Municipality', wards: 5 },
            { name: 'Gajuri', type: 'Rural Municipality', wards: 5 },
            { name: 'Galchi', type: 'Rural Municipality', wards: 5 },
            { name: 'Gangajamuna', type: 'Rural Municipality', wards: 5 },
            { name: 'Jwalamukhi', type: 'Rural Municipality', wards: 5 },
            { name: 'Thakre', type: 'Rural Municipality', wards: 5 },
            { name: 'Rubi Valley', type: 'Rural Municipality', wards: 5 },
            { name: 'Benighat Rorang', type: 'Rural Municipality', wards: 5 },
            { name: 'Siddhalekh', type: 'Rural Municipality', wards: 5 },
            { name: 'Tripurasundari', type: 'Rural Municipality', wards: 5 },
            { name: 'Netrawati', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Kathmandu',
          name_np: 'काठमाडौं',
          municipalities: [
            { name: 'Kathmandu', type: 'Metropolitan City', wards: 32 },
            { name: 'Kirtipur', type: 'Municipality', wards: 10 },
            { name: 'Budhanilkhantha', type: 'Municipality', wards: 13 },
            { name: 'Chandragiri', type: 'Municipality', wards: 11 },
            { name: 'Dakshinkali', type: 'Municipality', wards: 9 },
            { name: 'Gokarneshwar', type: 'Municipality', wards: 9 },
            { name: 'Nagarjun', type: 'Municipality', wards: 10 },
            { name: 'Shankharapur', type: 'Municipality', wards: 9 },
            { name: 'Tarakeshwar', type: 'Municipality', wards: 11 },
            { name: 'Tokha', type: 'Municipality', wards: 11 }
          ]
        },
        {
          name: 'Kavrepalanchok',
          name_np: 'काभ्रेपलान्चोक',
          municipalities: [
            { name: 'Dhulikhel', type: 'Municipality', wards: 12 },
            { name: 'Banepa', type: 'Municipality', wards: 12 },
            { name: 'Panauti', type: 'Municipality', wards: 10 },
            { name: 'Panchkhal', type: 'Municipality', wards: 9 },
            { name: 'Mandan Deupur', type: 'Municipality', wards: 9 },
            { name: 'Namobuddha', type: 'Municipality', wards: 10 },
            { name: 'Khanikhola', type: 'Municipality', wards: 9 },
            { name: 'Chauri Deurali', type: 'Rural Municipality', wards: 5 },
            { name: 'Temal', type: 'Rural Municipality', wards: 5 },
            { name: 'Bethanchowk', type: 'Rural Municipality', wards: 5 },
            { name: 'Bhumlu', type: 'Rural Municipality', wards: 5 },
            { name: 'Roshni', type: 'Rural Municipality', wards: 5 },
            { name: 'Mahabharat', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Lalitpur',
          name_np: 'ललितपुर',
          municipalities: [
            { name: 'Lalitpur', type: 'Metropolitan City', wards: 29 },
            { name: 'Godawari', type: 'Municipality', wards: 10 },
            { name: 'Konjyosom', type: 'Rural Municipality', wards: 5 },
            { name: 'Bagmati', type: 'Rural Municipality', wards: 5 },
            { name: 'Mahankal', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Nuwakot',
          name_np: 'नुवाकोट',
          municipalities: [
            { name: 'Bidur', type: 'Municipality', wards: 13 },
            { name: 'Belkotgadhi', type: 'Municipality', wards: 9 },
            { name: 'Kakani', type: 'Rural Municipality', wards: 5 },
            { name: 'Panchakanya', type: 'Rural Municipality', wards: 5 },
            { name: 'Likhu', type: 'Rural Municipality', wards: 5 },
            { name: 'Dupcheshwar', type: 'Rural Municipality', wards: 5 },
            { name: 'Shivapuri', type: 'Rural Municipality', wards: 5 },
            { name: 'Tadi', type: 'Rural Municipality', wards: 5 },
            { name: 'Suryagadhi', type: 'Rural Municipality', wards: 5 },
            { name: 'Kispang', type: 'Rural Municipality', wards: 5 },
            { name: 'Myagang', type: 'Rural Municipality', wards: 5 },
            { name: 'Tarakeshwar', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Rasuwa',
          name_np: 'रसुवा',
          municipalities: [
            { name: 'Uttargaya', type: 'Rural Municipality', wards: 5 },
            { name: 'Kalika', type: 'Rural Municipality', wards: 5 },
            { name: 'Gosaikunda', type: 'Rural Municipality', wards: 5 },
            { name: 'Naubesi', type: 'Rural Municipality', wards: 5 },
            { name: 'Parbatikunda', type: 'Rural Municipality', wards: 5 },
            { name: 'Amachodingmo', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Sindhupalchok',
          name_np: 'सिन्धुपाल्चोक',
          municipalities: [
            { name: 'Chautara Sangachowkgadhi', type: 'Municipality', wards: 12 },
            { name: 'Melamchi', type: 'Municipality', wards: 9 },
            { name: 'Indrawati', type: 'Rural Municipality', wards: 5 },
            { name: 'Bhotekoshi', type: 'Rural Municipality', wards: 5 },
            { name: 'Jugal', type: 'Rural Municipality', wards: 5 },
            { name: 'Panchpokhari Thangpal', type: 'Rural Municipality', wards: 5 },
            { name: 'Helambu', type: 'Rural Municipality', wards: 5 },
            { name: 'Balephi', type: 'Rural Municipality', wards: 5 },
            { name: 'Lisangkhu Pakhar', type: 'Rural Municipality', wards: 5 },
            { name: 'Tripurasundari', type: 'Rural Municipality', wards: 5 },
            { name: 'Sunkoshi', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Chitwan',
          name_np: 'चितवन',
          municipalities: [
            { name: 'Bharatpur', type: 'Metropolitan City', wards: 29 },
            { name: 'Ratnanagar', type: 'Municipality', wards: 14 },
            { name: 'Khairhani', type: 'Municipality', wards: 11 },
            { name: 'Kalika', type: 'Municipality', wards: 11 },
            { name: 'Madi', type: 'Municipality', wards: 9 },
            { name: 'Ichchhakamana', type: 'Rural Municipality', wards: 5 },
            { name: 'Rapti', type: 'Municipality', wards: 13 }
          ]
        }
      ]
    },
    {
      id: 4,
      name: 'Gandaki Pradesh',
      name_np: 'गण्डकी प्रदेश',
      districts: [
        {
          name: 'Baglung',
          name_np: 'बागलुङ',
          municipalities: [
            { name: 'Baglung', type: 'Municipality', wards: 13 },
            { name: 'Galkot', type: 'Municipality', wards: 11 },
            { name: 'Jaimini', type: 'Municipality', wards: 9 },
            { name: 'Dhorpatan', type: 'Municipality', wards: 9 },
            { name: 'Bareng', type: 'Rural Municipality', wards: 5 },
            { name: 'Kanthekhola', type: 'Rural Municipality', wards: 5 },
            { name: 'Taman Khola', type: 'Rural Municipality', wards: 5 },
            { name: 'Tara Khola', type: 'Rural Municipality', wards: 5 },
            { name: 'Nisikhola', type: 'Rural Municipality', wards: 5 },
            { name: 'Badigad', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Gorkha',
          name_np: 'गोरखा',
          municipalities: [
            { name: 'Gorkha', type: 'Municipality', wards: 13 },
            { name: 'Palungtar', type: 'Municipality', wards: 11 },
            { name: 'Sulikot', type: 'Rural Municipality', wards: 5 },
            { name: 'Siranchok', type: 'Rural Municipality', wards: 5 },
            { name: 'Ajirkot', type: 'Rural Municipality', wards: 5 },
            { name: 'Chumnubri', type: 'Rural Municipality', wards: 5 },
            { name: 'Bhimsen Thapa', type: 'Rural Municipality', wards: 5 },
            { name: 'Dharche', type: 'Rural Municipality', wards: 5 },
            { name: 'Sahid Lakhan', type: 'Rural Municipality', wards: 5 },
            { name: 'Baripak', type: 'Rural Municipality', wards: 5 },
            { name: 'Aarughat', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Kaski',
          name_np: 'कास्की',
          municipalities: [
            { name: 'Pokhara', type: 'Metropolitan City', wards: 33 },
            { name: 'Annapurna', type: 'Rural Municipality', wards: 5 },
            { name: 'Machhapuchchhre', type: 'Rural Municipality', wards: 5 },
            { name: 'Madi', type: 'Rural Municipality', wards: 5 },
            { name: 'Rupa', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Lamjung',
          name_np: 'लमजुङ',
          municipalities: [
            { name: 'Besisahar', type: 'Municipality', wards: 11 },
            { name: 'Rainas', type: 'Municipality', wards: 9 },
            { name: 'Sundarbazar', type: 'Municipality', wards: 9 },
            { name: 'Kwholasothar', type: 'Rural Municipality', wards: 5 },
            { name: 'Madhya Nepal', type: 'Rural Municipality', wards: 5 },
            { name: 'Dordi', type: 'Rural Municipality', wards: 5 },
            { name: 'Marsyangdi', type: 'Rural Municipality', wards: 5 },
            { name: 'Dudhpokhari', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Manang',
          name_np: 'मनाङ',
          municipalities: [
            { name: 'Chame', type: 'Rural Municipality', wards: 5 },
            { name: 'Nashong', type: 'Rural Municipality', wards: 5 },
            { name: 'Narpa Bhumi', type: 'Rural Municipality', wards: 5 },
            { name: 'Manang Ngisyang', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Mustang',
          name_np: 'मुस्ताङ',
          municipalities: [
            { name: 'Gharpajhong', type: 'Rural Municipality', wards: 5 },
            { name: 'Thasang', type: 'Rural Municipality', wards: 5 },
            { name: 'Barhagaun Muktichhetra', type: 'Rural Municipality', wards: 5 },
            { name: 'Lomanthang', type: 'Rural Municipality', wards: 5 },
            { name: 'Dalome', type: 'Rural Municipality', wards: 5 },
            { name: 'Waragung Muktichhetra', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Myagdi',
          name_np: 'म्याग्दी',
          municipalities: [
            { name: 'Beni', type: 'Municipality', wards: 9 },
            { name: 'Annapurna', type: 'Rural Municipality', wards: 5 },
            { name: 'Malika', type: 'Rural Municipality', wards: 5 },
            { name: 'Mangala', type: 'Rural Municipality', wards: 5 },
            { name: 'Raghuganga', type: 'Rural Municipality', wards: 5 },
            { name: 'Dhaulagiri', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Nawalpur',
          name_np: 'नवलपुर',
          municipalities: [
            { name: 'Kawasoti', type: 'Municipality', wards: 13 },
            { name: 'Gaindakot', type: 'Municipality', wards: 11 },
            { name: 'Devachuli', type: 'Municipality', wards: 11 },
            { name: 'Madhyabindu', type: 'Municipality', wards: 11 },
            { name: 'Bardaghat', type: 'Municipality', wards: 12 },
            { name: 'Ramgram', type: 'Municipality', wards: 11 },
            { name: 'Sunwal', type: 'Municipality', wards: 11 },
            { name: 'Hupsekot', type: 'Rural Municipality', wards: 5 },
            { name: 'Bulingtar', type: 'Rural Municipality', wards: 5 },
            { name: 'Binayi Tribeni', type: 'Rural Municipality', wards: 5 },
            { name: 'Baudeepur', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Parbat',
          name_np: 'पर्वत',
          municipalities: [
            { name: 'Kusma', type: 'Municipality', wards: 11 },
            { name: 'Phalewas', type: 'Municipality', wards: 11 },
            { name: 'Jaljala', type: 'Rural Municipality', wards: 5 },
            { name: 'Paiyun', type: 'Rural Municipality', wards: 5 },
            { name: 'Mahashila', type: 'Rural Municipality', wards: 5 },
            { name: 'Bihadi', type: 'Rural Municipality', wards: 5 },
            { name: 'Modi', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Syangja',
          name_np: 'स्याङ्जा',
          municipalities: [
            { name: 'Putalibazar', type: 'Municipality', wards: 13 },
            { name: 'Waling', type: 'Municipality', wards: 13 },
            { name: 'Bhirkot', type: 'Municipality', wards: 9 },
            { name: 'Galyang', type: 'Municipality', wards: 11 },
            { name: 'Chapakot', type: 'Municipality', wards: 9 },
            { name: 'Aandhikhola', type: 'Rural Municipality', wards: 6 },
            { name: 'Arjun Chaupari', type: 'Rural Municipality', wards: 7 },
            { name: 'Phedikhola', type: 'Rural Municipality', wards: 5 },
            { name: 'Kaligandaki', type: 'Rural Municipality', wards: 5 },
            { name: 'Harinas', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Tanahun',
          name_np: 'तनहुँ',
          municipalities: [
            { name: 'Damauli', type: 'Municipality', wards: 13 },
            { name: 'Byas', type: 'Municipality', wards: 13 },
            { name: 'Bhanu', type: 'Municipality', wards: 11 },
            { name: 'Shuklagandaki', type: 'Municipality', wards: 9 },
            { name: 'Anbu Khaireni', type: 'Rural Municipality', wards: 5 },
            { name: 'Devghat', type: 'Rural Municipality', wards: 5 },
            { name: 'Bandipur', type: 'Rural Municipality', wards: 5 },
            { name: 'Rishing', type: 'Rural Municipality', wards: 5 },
            { name: 'Myagde', type: 'Rural Municipality', wards: 5 },
            { name: 'Ghiring', type: 'Rural Municipality', wards: 5 }
          ]
        }
      ]
    },
    {
      id: 5,
      name: 'Lumbini Pradesh',
      name_np: 'लुम्बिनी प्रदेश',
      districts: [
        {
          name: 'Arghakhanchi',
          name_np: 'अर्घाखाँची',
          municipalities: [
            { name: 'Sandhikharka', type: 'Municipality', wards: 11 },
            { name: 'Sitganga', type: 'Municipality', wards: 13 },
            { name: 'Bhumekasthan', type: 'Municipality', wards: 9 },
            { name: 'Chhatradev', type: 'Rural Municipality', wards: 5 },
            { name: 'Panini', type: 'Rural Municipality', wards: 5 },
            { name: 'Malarani', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Banke',
          name_np: 'बाँके',
          municipalities: [
            { name: 'Nepalgunj', type: 'Sub-Metropolitan City', wards: 23 },
            { name: 'Kohalpur', type: 'Municipality', wards: 11 },
            { name: 'Narainapur', type: 'Rural Municipality', wards: 5 },
            { name: 'Rapti Sonari', type: 'Rural Municipality', wards: 5 },
            { name: 'Baijanath', type: 'Rural Municipality', wards: 5 },
            { name: 'Khajura', type: 'Rural Municipality', wards: 5 },
            { name: 'Duduwa', type: 'Rural Municipality', wards: 5 },
            { name: 'Janaki', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Bardiya',
          name_np: 'बर्दिया',
          municipalities: [
            { name: 'Gulariya', type: 'Municipality', wards: 12 },
            { name: 'Madhuwan', type: 'Municipality', wards: 9 },
            { name: 'Rajapur', type: 'Municipality', wards: 9 },
            { name: 'Thakurbaba', type: 'Municipality', wards: 9 },
            { name: 'Bansgadhi', type: 'Municipality', wards: 9 },
            { name: 'Barbardiya', type: 'Municipality', wards: 11 },
            { name: 'Geruwa', type: 'Rural Municipality', wards: 6 },
            { name: 'Badhaiyatal', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Dang',
          name_np: 'दाङ',
          municipalities: [
            { name: 'Ghorahi', type: 'Sub-Metropolitan City', wards: 19 },
            { name: 'Tulsipur', type: 'Sub-Metropolitan City', wards: 19 },
            { name: 'Lamahi', type: 'Municipality', wards: 9 },
            { name: 'Banglachuli', type: 'Rural Municipality', wards: 5 },
            { name: 'Dangi Gadhawa', type: 'Rural Municipality', wards: 5 },
            { name: 'Shantinagar', type: 'Rural Municipality', wards: 5 },
            { name: 'Babai', type: 'Rural Municipality', wards: 5 },
            { name: 'Rapti', type: 'Rural Municipality', wards: 5 },
            { name: 'Gadhawa', type: 'Rural Municipality', wards: 5 },
            { name: 'Rajpur', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Pyuthan',
          name_np: 'प्युठान',
          municipalities: [
            { name: 'Pyuthan', type: 'Municipality', wards: 11 },
            { name: 'Swargadwari', type: 'Municipality', wards: 9 },
            { name: 'Gaumukhi', type: 'Rural Municipality', wards: 5 },
            { name: 'Mallarani', type: 'Rural Municipality', wards: 5 },
            { name: 'Naubahini', type: 'Rural Municipality', wards: 5 },
            { name: 'Jhimruk', type: 'Rural Municipality', wards: 5 },
            { name: 'Sarumarani', type: 'Rural Municipality', wards: 5 },
            { name: 'Ayirabati', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Rolpa',
          name_np: 'रोल्पा',
          municipalities: [
            { name: 'Liwang', type: 'Municipality', wards: 11 },
            { name: 'Rolpa', type: 'Municipality', wards: 9 },
            { name: 'Triveni', type: 'Rural Municipality', wards: 5 },
            { name: 'Gangadev', type: 'Rural Municipality', wards: 5 },
            { name: 'Madi', type: 'Rural Municipality', wards: 5 },
            { name: 'Sunchhahari', type: 'Rural Municipality', wards: 5 },
            { name: 'Thawang', type: 'Rural Municipality', wards: 5 },
            { name: 'Runtigadhi', type: 'Rural Municipality', wards: 5 },
            { name: 'Pariwartan', type: 'Rural Municipality', wards: 5 },
            { name: 'Duikholi', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'East Rukum',
          name_np: 'पूर्वी रुकुम',
          municipalities: [
            { name: 'Putha Uttarganga', type: 'Rural Municipality', wards: 5 },
            { name: 'Bhume', type: 'Rural Municipality', wards: 5 },
            { name: 'Sisne', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Gulmi',
          name_np: 'गुल्मी',
          municipalities: [
            { name: 'Tamghas', type: 'Municipality', wards: 11 },
            { name: 'Musikot', type: 'Municipality', wards: 9 },
            { name: 'Resunga', type: 'Municipality', wards: 11 },
            { name: 'Dhurkot', type: 'Rural Municipality', wards: 5 },
            { name: 'Gulmi Darbar', type: 'Rural Municipality', wards: 5 },
            { name: 'Satyawati', type: 'Rural Municipality', wards: 5 },
            { name: 'Chandrakot', type: 'Rural Municipality', wards: 5 },
            { name: 'Rurukot', type: 'Rural Municipality', wards: 5 },
            { name: 'Chhatrakot', type: 'Rural Municipality', wards: 5 },
            { name: 'Isma', type: 'Rural Municipality', wards: 5 },
            { name: 'Malika', type: 'Rural Municipality', wards: 5 },
            { name: 'Madane', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Kapilvastu',
          name_np: 'कपिलवस्तु',
          municipalities: [
            { name: 'Kapilvastu', type: 'Municipality', wards: 11 },
            { name: 'Banganga', type: 'Municipality', wards: 11 },
            { name: 'Buddhabhumi', type: 'Municipality', wards: 9 },
            { name: 'Shivaraj', type: 'Municipality', wards: 11 },
            { name: 'Krishnanagar', type: 'Municipality', wards: 12 },
            { name: 'Maharajganj', type: 'Municipality', wards: 9 },
            { name: 'Mayadevi', type: 'Rural Municipality', wards: 5 },
            { name: 'Yashodhara', type: 'Rural Municipality', wards: 5 },
            { name: 'Suddhodhan', type: 'Rural Municipality', wards: 5 },
            { name: 'Bijaynagar', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Parasi',
          name_np: 'परासी',
          municipalities: [
            { name: 'Ramgram', type: 'Municipality', wards: 13 },
            { name: 'Sunwal', type: 'Municipality', wards: 11 },
            { name: 'Bardaghat', type: 'Municipality', wards: 12 },
            { name: 'Palhi Nandan', type: 'Rural Municipality', wards: 5 },
            { name: 'Pratappur', type: 'Rural Municipality', wards: 5 },
            { name: 'Binayi Tribeni', type: 'Rural Municipality', wards: 5 },
            { name: 'Sarawal', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Palpa',
          name_np: 'पाल्पा',
          municipalities: [
            { name: 'Tansen', type: 'Municipality', wards: 14 },
            { name: 'Rampur', type: 'Municipality', wards: 9 },
            { name: 'Nisdi', type: 'Rural Municipality', wards: 5 },
            { name: 'Purbakhola', type: 'Rural Municipality', wards: 5 },
            { name: 'Rambha', type: 'Rural Municipality', wards: 5 },
            { name: 'Mathagadhi', type: 'Rural Municipality', wards: 5 },
            { name: 'Tinahu', type: 'Rural Municipality', wards: 5 },
            { name: 'Bagnaskali', type: 'Rural Municipality', wards: 5 },
            { name: 'Ripdikot', type: 'Rural Municipality', wards: 5 },
            { name: 'Rainadevi Chhahara', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Rupandehi',
          name_np: 'रुपन्देही',
          municipalities: [
            { name: 'Butwal', type: 'Sub-Metropolitan City', wards: 19 },
            { name: 'Siddharthanagar', type: 'Municipality', wards: 13 },
            { name: 'Sainamaina', type: 'Municipality', wards: 11 },
            { name: 'Devdaha', type: 'Municipality', wards: 11 },
            { name: 'Lumbini Sanskritik', type: 'Municipality', wards: 11 },
            { name: 'Tilottama', type: 'Municipality', wards: 13 },
            { name: 'Omsatiya', type: 'Rural Municipality', wards: 5 },
            { name: 'Rohini', type: 'Rural Municipality', wards: 5 },
            { name: 'Marchawarimai', type: 'Rural Municipality', wards: 5 },
            { name: 'Siyari', type: 'Rural Municipality', wards: 5 },
            { name: 'Suddhodhan', type: 'Rural Municipality', wards: 5 },
            { name: 'Kanchan', type: 'Rural Municipality', wards: 5 },
            { name: 'Mayadevi', type: 'Rural Municipality', wards: 5 },
            { name: 'Kotahimai', type: 'Rural Municipality', wards: 5 },
            { name: 'Sammarimai', type: 'Rural Municipality', wards: 5 },
            { name: 'Gaidahawa', type: 'Rural Municipality', wards: 5 }
          ]
        }
      ]
    },
    {
      id: 6,
      name: 'Karnali Pradesh',
      name_np: 'कर्णाली प्रदेश',
      districts: [
        {
          name: 'Western Rukum',
          name_np: 'पश्चिमी रुकुम',
          municipalities: [
            { name: 'Musikot', type: 'Municipality', wards: 13 },
            { name: 'Chaurjahari', type: 'Municipality', wards: 9 },
            { name: 'Aathabis Kot', type: 'Rural Municipality', wards: 5 },
            { name: 'Banphikot', type: 'Rural Municipality', wards: 5 },
            { name: 'Triveni', type: 'Rural Municipality', wards: 5 },
            { name: 'Sisne', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Salyan',
          name_np: 'सल्यान',
          municipalities: [
            { name: 'Shaarda', type: 'Municipality', wards: 11 },
            { name: 'Bagchaur', type: 'Municipality', wards: 9 },
            { name: 'Banagad Kupinde', type: 'Municipality', wards: 11 },
            { name: 'Kalimati', type: 'Rural Municipality', wards: 5 },
            { name: 'Triveni', type: 'Rural Municipality', wards: 5 },
            { name: 'Kapurkot', type: 'Rural Municipality', wards: 5 },
            { name: 'Chhatreshwari', type: 'Rural Municipality', wards: 5 },
            { name: 'Dhorchaur', type: 'Rural Municipality', wards: 5 },
            { name: 'Kumakh', type: 'Rural Municipality', wards: 5 },
            { name: 'Marmaa', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Dolpa',
          name_np: 'डोल्पा',
          municipalities: [
            { name: 'Thuli Bheri', type: 'Municipality', wards: 9 },
            { name: 'Tripurasundari', type: 'Municipality', wards: 9 },
            { name: 'Dolpo Buddha', type: 'Rural Municipality', wards: 5 },
            { name: 'Kaike', type: 'Rural Municipality', wards: 5 },
            { name: 'Mudke Chula', type: 'Rural Municipality', wards: 5 },
            { name: 'She Phoksundo', type: 'Rural Municipality', wards: 5 },
            { name: 'Jagadulla', type: 'Rural Municipality', wards: 5 },
            { name: 'Chharka Tangsong', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Humla',
          name_np: 'हुम्ला',
          municipalities: [
            { name: 'Simkot', type: 'Rural Municipality', wards: 5 },
            { name: 'Sarkegad', type: 'Rural Municipality', wards: 5 },
            { name: 'Adanchuli', type: 'Rural Municipality', wards: 5 },
            { name: 'Kharpunath', type: 'Rural Municipality', wards: 5 },
            { name: 'Tanjakot', type: 'Rural Municipality', wards: 5 },
            { name: 'Chankheli', type: 'Rural Municipality', wards: 5 },
            { name: 'Namkha', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Jumla',
          name_np: 'जुम्ला',
          municipalities: [
            { name: 'Chandannath', type: 'Municipality', wards: 9 },
            { name: 'Kartikey', type: 'Rural Municipality', wards: 5 },
            { name: 'Tatopani', type: 'Rural Municipality', wards: 5 },
            { name: 'Patarasi', type: 'Rural Municipality', wards: 5 },
            { name: 'Tila', type: 'Rural Municipality', wards: 5 },
            { name: 'Kanakasundari', type: 'Rural Municipality', wards: 5 },
            { name: 'Sinja', type: 'Rural Municipality', wards: 5 },
            { name: 'Hima', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Kalikot',
          name_np: 'कालिकोट',
          municipalities: [
            { name: 'Khandachakra', type: 'Municipality', wards: 9 },
            { name: 'Raskot', type: 'Municipality', wards: 9 },
            { name: 'Tilagufa', type: 'Municipality', wards: 9 },
            { name: 'Pachal Jharana', type: 'Rural Municipality', wards: 5 },
            { name: 'Sanni Triveni', type: 'Rural Municipality', wards: 5 },
            { name: 'Naraharinath', type: 'Rural Municipality', wards: 5 },
            { name: 'Shubha Kalika', type: 'Rural Municipality', wards: 5 },
            { name: 'Mahawai', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Mugu',
          name_np: 'मुगु',
          municipalities: [
            { name: 'Chhayanath Rara', type: 'Municipality', wards: 9 },
            { name: 'Mugum Karmarong', type: 'Rural Municipality', wards: 5 },
            { name: 'Soru', type: 'Rural Municipality', wards: 5 },
            { name: 'Khatyad', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Surkhet',
          name_np: 'सुर्खेत',
          municipalities: [
            { name: 'Birendranagar', type: 'Municipality', wards: 14 },
            { name: 'Bheriganga', type: 'Municipality', wards: 9 },
            { name: 'Gurbhakot', type: 'Municipality', wards: 11 },
            { name: 'Panchapuri', type: 'Municipality', wards: 9 },
            { name: 'Lekbeshi', type: 'Municipality', wards: 9 },
            { name: 'Chaukune', type: 'Rural Municipality', wards: 5 },
            { name: 'Barahatal', type: 'Rural Municipality', wards: 5 },
            { name: 'Simta', type: 'Rural Municipality', wards: 5 },
            { name: 'Saskhar', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Dailekh',
          name_np: 'दैलेख',
          municipalities: [
            { name: 'Narayan', type: 'Municipality', wards: 9 },
            { name: 'Dullu', type: 'Municipality', wards: 11 },
            { name: 'Chamunda Bindrasaini', type: 'Municipality', wards: 9 },
            { name: 'Aathabis', type: 'Municipality', wards: 9 },
            { name: 'Bhagawatimai', type: 'Rural Municipality', wards: 5 },
            { name: 'Dungeshwar', type: 'Rural Municipality', wards: 5 },
            { name: 'Gurans', type: 'Rural Municipality', wards: 5 },
            { name: 'Naumule', type: 'Rural Municipality', wards: 5 },
            { name: 'Mahabu', type: 'Rural Municipality', wards: 5 },
            { name: 'Thantikandh', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Jajarkot',
          name_np: 'जाजरकोट',
          municipalities: [
            { name: 'Bheri', type: 'Municipality', wards: 13 },
            { name: 'Chhedagad', type: 'Municipality', wards: 9 },
            { name: 'Nalagad', type: 'Municipality', wards: 9 },
            { name: 'Kuse', type: 'Rural Municipality', wards: 5 },
            { name: 'Junichande', type: 'Rural Municipality', wards: 5 },
            { name: 'Shivalaya', type: 'Rural Municipality', wards: 5 },
            { name: 'Barekot', type: 'Rural Municipality', wards: 5 }
          ]
        }
      ]
    },
    {
      id: 7,
      name: 'Sudurpashchim Pradesh',
      name_np: 'सुदूरपश्चिम प्रदेश',
      districts: [
        {
          name: 'Kailali',
          name_np: 'कैलाली',
          municipalities: [
            { name: 'Dhangadhi', type: 'Sub-Metropolitan City', wards: 19 },
            { name: 'Tikapur', type: 'Municipality', wards: 11 },
            { name: 'Ghodaghodi', type: 'Municipality', wards: 11 },
            { name: 'Lamkichuha', type: 'Municipality', wards: 9 },
            { name: 'Bhajani', type: 'Municipality', wards: 9 },
            { name: 'Godawari', type: 'Municipality', wards: 11 },
            { name: 'Kailari', type: 'Rural Municipality', wards: 5 },
            { name: 'Gauriganga', type: 'Municipality', wards: 11 },
            { name: 'Bardagoriya', type: 'Rural Municipality', wards: 5 },
            { name: 'Mohanyal', type: 'Rural Municipality', wards: 5 },
            { name: 'Joshipur', type: 'Rural Municipality', wards: 5 },
            { name: 'Chure', type: 'Rural Municipality', wards: 5 },
            { name: 'Janaki', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Achham',
          name_np: 'अछाम',
          municipalities: [
            { name: 'Mangalsen', type: 'Municipality', wards: 11 },
            { name: 'Kamalbazar', type: 'Municipality', wards: 9 },
            { name: 'Panchdewal Binayak', type: 'Municipality', wards: 9 },
            { name: 'Chaurpati', type: 'Rural Municipality', wards: 5 },
            { name: 'Mellekh', type: 'Rural Municipality', wards: 5 },
            { name: 'Bannigadhi Jayagadh', type: 'Rural Municipality', wards: 5 },
            { name: 'Ramaroshan', type: 'Rural Municipality', wards: 5 },
            { name: 'Dhakari', type: 'Rural Municipality', wards: 5 },
            { name: 'Turmakhand', type: 'Rural Municipality', wards: 5 },
            { name: 'Sanphebagar', type: 'Municipality', wards: 11 }
          ]
        },
        {
          name: 'Doti',
          name_np: 'डोटी',
          municipalities: [
            { name: 'Dipayal Silgadhi', type: 'Municipality', wards: 11 },
            { name: 'Shikhar', type: 'Municipality', wards: 11 },
            { name: 'Purbichauki', type: 'Rural Municipality', wards: 5 },
            { name: 'Badikedar', type: 'Rural Municipality', wards: 5 },
            { name: 'Jorayal', type: 'Rural Municipality', wards: 5 },
            { name: 'Sayal', type: 'Rural Municipality', wards: 5 },
            { name: 'Adarsha', type: 'Rural Municipality', wards: 5 },
            { name: 'Bogatan Phudsil', type: 'Rural Municipality', wards: 5 },
            { name: 'K I Singh', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Bajhang',
          name_np: 'बझाङ',
          municipalities: [
            { name: 'Jayaprithvi', type: 'Municipality', wards: 11 },
            { name: 'Bungal', type: 'Municipality', wards: 9 },
            { name: 'Talkot', type: 'Rural Municipality', wards: 5 },
            { name: 'Masta', type: 'Rural Municipality', wards: 5 },
            { name: 'Khaptad Chhededaha', type: 'Rural Municipality', wards: 5 },
            { name: 'Thalara', type: 'Rural Municipality', wards: 5 },
            { name: 'Bitthadchir', type: 'Rural Municipality', wards: 5 },
            { name: 'Surma', type: 'Rural Municipality', wards: 5 },
            { name: 'Chabis Pathibhara', type: 'Rural Municipality', wards: 5 },
            { name: 'Durgathali', type: 'Rural Municipality', wards: 5 },
            { name: 'Kedarsyun', type: 'Rural Municipality', wards: 5 },
            { name: 'Sai Paal', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Bajura',
          name_np: 'बाजुरा',
          municipalities: [
            { name: 'Budhinanda', type: 'Municipality', wards: 9 },
            { name: 'Badimalika', type: 'Municipality', wards: 9 },
            { name: 'Triveni', type: 'Municipality', wards: 9 },
            { name: 'Budhiganga', type: 'Municipality', wards: 9 },
            { name: 'Khaptad', type: 'Rural Municipality', wards: 5 },
            { name: 'Gaumul', type: 'Rural Municipality', wards: 5 },
            { name: 'Swami Kartik', type: 'Rural Municipality', wards: 5 },
            { name: 'Dogadedi', type: 'Rural Municipality', wards: 5 },
            { name: 'Himali', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Kanchanpur',
          name_np: 'कञ्चनपुर',
          municipalities: [
            { name: 'Bhimdatta', type: 'Municipality', wards: 19 },
            { name: 'Punarbas', type: 'Municipality', wards: 11 },
            { name: 'Bedkot', type: 'Municipality', wards: 10 },
            { name: 'Mahakali', type: 'Municipality', wards: 10 },
            { name: 'Shuklaphanta', type: 'Municipality', wards: 10 },
            { name: 'Belauri', type: 'Municipality', wards: 9 },
            { name: 'Krishnapur', type: 'Municipality', wards: 9 },
            { name: 'Laljhadi', type: 'Rural Municipality', wards: 5 },
            { name: 'Beldandi', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Dadeldhura',
          name_np: 'डडेल्धुरा',
          municipalities: [
            { name: 'Amargadhi', type: 'Municipality', wards: 11 },
            { name: 'Parshuram', type: 'Municipality', wards: 11 },
            { name: 'Aalital', type: 'Rural Municipality', wards: 5 },
            { name: 'Bhageshwar', type: 'Rural Municipality', wards: 5 },
            { name: 'Nawadurga', type: 'Rural Municipality', wards: 5 },
            { name: 'Ajayameru', type: 'Rural Municipality', wards: 5 },
            { name: 'Ganyapadhura', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Baitadi',
          name_np: 'बैतडी',
          municipalities: [
            { name: 'Dasharathchand', type: 'Municipality', wards: 11 },
            { name: 'Patan', type: 'Municipality', wards: 9 },
            { name: 'Melauli', type: 'Municipality', wards: 9 },
            { name: 'Purchaudi', type: 'Municipality', wards: 9 },
            { name: 'Surnaya', type: 'Rural Municipality', wards: 5 },
            { name: 'Shivanath', type: 'Rural Municipality', wards: 5 },
            { name: 'Sigas', type: 'Rural Municipality', wards: 5 },
            { name: 'Dilashaini', type: 'Rural Municipality', wards: 5 },
            { name: 'Dogdakedar', type: 'Rural Municipality', wards: 5 },
            { name: 'Pancheshwar', type: 'Rural Municipality', wards: 5 }
          ]
        },
        {
          name: 'Darchula',
          name_np: 'दार्चुला',
          municipalities: [
            { name: 'Mahakali', type: 'Municipality', wards: 9 },
            { name: 'Shailyana', type: 'Rural Municipality', wards: 5 },
            { name: 'Malikarjun', type: 'Rural Municipality', wards: 5 },
            { name: 'Apihimal', type: 'Rural Municipality', wards: 5 },
            { name: 'Duhun', type: 'Rural Municipality', wards: 5 },
            { name: 'Naugad', type: 'Rural Municipality', wards: 5 },
            { name: 'Marma', type: 'Rural Municipality', wards: 5 },
            { name: 'Lekam', type: 'Rural Municipality', wards: 5 },
            { name: 'Vyans', type: 'Rural Municipality', wards: 5 }
          ]
        }
      ]
    }
  ]
};

/**
 * Get all province names
 */
function getProvinces() {
  return NEPAL_ADDRESS.provinces.map(function(p) {
    return { id: p.id, name: p.name, name_np: p.name_np };
  });
}

/**
 * Get districts by province id (1-7)
 */
function getDistricts(provinceId) {
  var province = NEPAL_ADDRESS.provinces.find(function(p) { return p.id === provinceId; });
  if (!province) return [];
  return province.districts.map(function(d) {
    return { name: d.name, name_np: d.name_np };
  });
}

/**
 * Get municipalities by province id and district name
 */
function getMunicipalities(provinceId, districtName) {
  var province = NEPAL_ADDRESS.provinces.find(function(p) { return p.id === provinceId; });
  if (!province) return [];
  var district = province.districts.find(function(d) { return d.name === districtName; });
  if (!district) return [];
  return district.municipalities.map(function(m) {
    return { name: m.name, type: m.type, wards: m.wards };
  });
}

/**
 * Get all districts (flat list) by province id
 */
function getDistrictNames(provinceId) {
  return getDistricts(provinceId).map(function(d) { return d.name; });
}

/**
 * Get municipality names (flat list) by province + district
 */
function getMunicipalityNames(provinceId, districtName) {
  return getMunicipalities(provinceId, districtName).map(function(m) { return m.name; });
}

/**
 * Build a formatted address string from components
 */
function buildAddressString(street, municipality, ward, district, province) {
  var parts = [];
  if (street) parts.push(street);
  parts.push(municipality + '-' + ward);
  parts.push(district);
  parts.push(province);
  return parts.join(', ');
}
