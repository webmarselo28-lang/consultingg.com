/*
  # Add luxury Boyana property

  1. New Property
    - `prop-010` - Luxury house in Boyana with 360° panoramic views
    - Price: €21,700,000 (sale)
    - Area: 580.70 м² (decimal)
    - 5 images with proper sort order

  2. Images
    - 5 property images linked to the new property
    - image1.jpeg as main image
    - Proper sort order and paths

  3. Safety
    - Uses UUID generation for id column
    - Proper foreign key relationships
    - No hardcoded UUIDs
*/

-- Insert the luxury Boyana property (let PostgreSQL generate UUID for id)
INSERT INTO properties (
  title,
  description,
  price,
  currency,
  transaction_type,
  property_type,
  city_region,
  district,
  address,
  area,
  bedrooms,
  bathrooms,
  floors,
  floor_number,
  terraces,
  construction_type,
  condition_type,
  heating,
  exposure,
  year_built,
  furnishing_level,
  has_elevator,
  has_garage,
  has_southern_exposure,
  new_construction,
  featured,
  active,
  pricing_mode
) VALUES (
  'Луксозна самостоятелна къща в кв. Бояна | 580.70 кв.м РЗП | Двор 1200 кв.м | 360° панорама',
  '🏡 Луксозна самостоятелна къща в кв. Бояна | 580.70 кв.м РЗП | Двор 1200 кв.м | 360° панорама

Нова къща в процес на строителство, с планирано въвеждане в експлоатация през 2026 г., разположена в престижната част на кв. Бояна. Имотът се отличава с южно изложение, панорамни 360° гледки към Витоша, Ботаническата градина и цяла София, двор от 1200 кв.м с професионален ландшафтен проект, както и големи тераси със стъклени парапети, осигуряващи простор и светлина. 

👉 Къщата разполага с дизайнерски проект и 3D визуализации за баните, тоалетните и кухнята – изключително предимство за бъдещото довършване и обзавеждане на дома.

✔️ Основни характеристики:
 • РЗП: 580.70 кв.м
 • Двор: 1101 кв.м (ландшафтинг)
 • Строителство: тухла Wienerberger
 • Дограма: алуминиева ETEM, троен шумоизолиращ стъклопакет
 • Отопление и охлаждане: термопомпа Daikin, газово котле, 500 л бойлер, подово отопление, конвектори
 • Газ: Да

🔻 Подземно ниво :
 • Гараж за 2/3 автомобила с луксозни врати BENINCA
 • Широка рампа с павета за 4–6 автомобила
 • Котелно помещение, малък склад
 • Фитнес със сауна, парна баня, баня и тоалетна

🔸 Първи етаж (партер):
 • Тоалетна за гости
 • Голям дрешник и перално помещение
 • Стая за гости или персонал
 • Просторен хол с камина
 • Кухня със складово помещение
 • Големи тераси със стъклени парапети

🔸 Втори етаж:
 • Родителска спалня с три отделни помещения: баня, тоалетна и дрешник
 • Две детски стаи, всяка със собствена баня и дрешник
 • Излаз към панорамни тераси

🌇 Панорамен покрив:
 • Вътрешен достъп
 • 360° гледка към София и Витоша
 • Изводи за ток и вода
 • Подходящ за лятна кухня, бар, roof-top зона или басейн

🎯 Предимства:
 • Топ локация
 • Дизайнерски проект с визуализации за бани, тоалетни и кухня
 • Простор, комфорт и гледки без аналог в София',
  21700000.00,
  'EUR',
  'sale',
  'КЪЩА',
  'София',
  'Бояна',
  'кв. Бояна',
  580.70,
  4,
  5,
  3,
  0,
  3,
  'Тухла',
  'Ново строителство',
  'Термопомпа',
  'Юг',
  2026,
  'none',
  false,
  true,
  true,
  true,
  false,
  true,
  'total'
);

-- Get the generated UUID for the property we just inserted
DO $$
DECLARE
    new_property_id UUID;
BEGIN
    -- Get the ID of the property we just inserted
    SELECT id INTO new_property_id 
    FROM properties 
    WHERE title = 'Луксозна самостоятелна къща в кв. Бояна | 580.70 кв.м РЗП | Двор 1200 кв.м | 360° панорама'
    ORDER BY created_at DESC 
    LIMIT 1;

    -- Insert the 5 images linked to this property
    INSERT INTO property_images (
        property_id,
        image_url,
        image_path,
        alt_text,
        sort_order,
        is_main
    ) VALUES 
    (new_property_id, '/images/prop-010/image1.jpeg', '/images/prop-010/image1.jpeg', 'Luxury Boyana house - exterior view', 1, true),
    (new_property_id, '/images/prop-010/image2.jpeg', '/images/prop-010/image2.jpeg', 'Luxury Boyana house - side view', 2, false),
    (new_property_id, '/images/prop-010/image3.jpeg', '/images/prop-010/image3.jpeg', 'Luxury Boyana house - rooftop terrace', 3, false),
    (new_property_id, '/images/prop-010/image4.jpeg', '/images/prop-010/image4.jpeg', 'Luxury Boyana house - evening view', 4, false),
    (new_property_id, '/images/prop-010/image5.jpeg', '/images/prop-010/image5.jpeg', 'Luxury Boyana house - architectural detail', 5, false);
END $$;