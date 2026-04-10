<?php
$page_title = "Политика обработки персональных данных | Dosatron";
include 'includes/content_parser.php';
function getContent($section) {
    require_once 'includes/content_parser.php';
    return getContentSection($section, '');
}

$meta_desc = getContent('meta_description');
$meta_keys = getContent('meta_keywords');
$favicon=getContent('favicon');
?>


<!DOCTYPE html>
<html lang="ru">
<head>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-P2N10VB842"></script>

<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-P2N10VB842');
</script>
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=108454352', 'ym');

    ym(108454352, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/108454352" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
    <meta charset="UTF-8">
    <link rel="icon" href="<?php echo $favicon; ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo $meta_desc; ?>" type="image/x-icon">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $meta_desc; ?>">
    <meta name="keywords" content="<?php echo $meta_keys; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cs/style.css">
    <link rel="stylesheet" href="cs/privacy.css">
    <script src="j/script.js?v=<?php echo filemtime('j/script.js'); ?>" defer></script> 
</head>

<body>
<?php include "includes/header.php"; ?>
<section class="policy-content">
    <div class="container">
        <h1 class="section-title">Политика обработки персональных данных</h1>
        <div class="content-wrapper">
            
            <section class="policy-section">
                <h2>1. Общие положения</h2>
                <p>Настоящая политика обработки персональных данных составлена в соответствии с требованиями Федерального закона от 27.07.2006. № 152-ФЗ «О персональных данных» (далее — Закон о персональных данных) и определяет порядок обработки персональных данных и меры по обеспечению безопасности персональных данных, предпринимаемые Dosatron.online (далее — Оператор).</p>
                
                <div class="subsection">
                    <h3>1.1.</h3>
                    <p>Оператор ставит своей важнейшей целью и условием осуществления своей деятельности соблюдение прав и свобод человека и гражданина при обработке его персональных данных, в том числе защиты прав на неприкосновенность частной жизни, личную и семейную тайну.</p>
                </div>
                
                <div class="subsection">
                    <h3>1.2.</h3>
                    <p>Настоящая политика Оператора в отношении обработки персональных данных (далее — Политика) применяется ко всей информации, которую Оператор может получить о посетителях веб-сайта <a href="https://dosatron.online/">https://dosatron.online/</a>.</p>
                </div>
            </section>

            <section class="policy-section">
                <h2>2. Основные понятия, используемые в Политике</h2>
                
                <div class="definitions-grid">
                    <div class="definition-item">
                        <h3>2.1. Автоматизированная обработка персональных данных</h3>
                        <p>обработка персональных данных с помощью средств вычислительной техники.</p>
                    </div>
                    
                    <div class="definition-item">
                        <h3>2.2. Блокирование персональных данных</h3>
                        <p>временное прекращение обработки персональных данных (за исключением случаев, если обработка необходима для уточнения персональных данных).</p>
                    </div>
                    
                    <div class="definition-item">
                        <h3>2.3. Веб-сайт</h3>
                        <p>совокупность графических и информационных материалов, а также программ для ЭВМ и баз данных, обеспечивающих их доступность в сети интернет по сетевому адресу <a href="https://dosatron.online/">https://dosatron.online/</a>.</p>
                    </div>
                    
                    <div class="definition-item">
                        <h3>2.4. Информационная система персональных данных</h3>
                        <p>совокупность содержащихся в базах данных персональных данных и обеспечивающих их обработку информационных технологий и технических средств.</p>
                    </div>
                    
                    <div class="definition-item">
                        <h3>2.5. Обезличивание персональных данных</h3>
                        <p>действия, в результате которых невозможно определить без использования дополнительной информации принадлежность персональных данных конкретному Пользователю или иному субъекту персональных данных.</p>
                    </div>
                    
                    <div class="definition-item">
                        <h3>2.6. Обработка персональных данных</h3>
                        <p>любое действие (операция) или совокупность действий (операций), совершаемых с использованием средств автоматизации или без использования таких средств с персональными данными, включая сбор, запись, систематизацию, накопление, хранение, уточнение (обновление, изменение), извлечение, использование, передачу (распространение, предоставление, доступ), обезличивание, блокирование, удаление, уничтожение персональных данных.</p>
                    </div>
                    
                    <div class="definition-item">
                        <h3>2.7. Оператор</h3>
                        <p>государственный орган, муниципальный орган, юридическое или физическое лицо, самостоятельно или совместно с другими лицами организующие и/или осуществляющие обработку персональных данных, а также определяющие цели обработки персональных данных, состав персональных данных, подлежащих обработке, действия (операции), совершаемые с персональными данными.</p>
                    </div>
                    
                    <div class="definition-item">
                        <h3>2.8. Персональные данные</h3>
                        <p>любая информация, относящаяся прямо или косвенно к определенному или определяемому Пользователю веб-сайта <a href="https://dosatron.online/">https://dosatron.online/</a>.</p>
                    </div>
                </div>
            </section>

            <section class="policy-section">
                <h2>3. Основные права и обязанности Оператора</h2>
                
                <div class="subsection">
                    <h3>3.1. Оператор имеет право:</h3>
                    <ul>
                        <li>получать от субъекта персональных данных достоверную информацию и/или документы, содержащие персональные данные;</li>
                        <li>в случае отзыва субъектом персональных данных согласия на обработку персональных данных, продолжить обработку без согласия субъекта при наличии оснований, указанных в Законе о персональных данных;</li>
                        <li>самостоятельно определять состав мер, необходимых и достаточных для выполнения обязанностей, предусмотренных Законом о персональных данных.</li>
                    </ul>
                </div>
                
                <div class="subsection">
                    <h3>3.2. Оператор обязан:</h3>
                    <ul>
                        <li>предоставлять субъекту по его запросу информацию об обработке его персональных данных;</li>
                        <li>организовывать обработку персональных данных в установленном законодательством РФ порядке;</li>
                        <li>отвечать на обращения и запросы субъектов персональных данных и их законных представителей;</li>
                        <li>сообщать в уполномоченный орган по защите прав субъектов персональных данных необходимую информацию в течение 10 дней с даты получения запроса;</li>
                        <li>публиковать или обеспечивать доступ к настоящей Политике;</li>
                        <li>принимать меры для защиты персональных данных от неправомерного доступа, уничтожения, изменения и других неправомерных действий;</li>
                        <li>прекращать обработку и уничтожать персональные данные в случаях, предусмотренных Законом;</li>
                        <li>исполнять иные обязанности, предусмотренные Законом о персональных данных.</li>
                    </ul>
                </div>
            </section>

            <section class="policy-section">
                <h2>6. Цели обработки персональных данных</h2>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Цель обработки</th>
                                <th>Персональные данные</th>
                                <th>Правовые основания</th>
                                <th>Виды обработки</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Уточнение деталей заказа для формирования коммерческого предложения</td>
                                <td>
                                    <ul>
                                        <li>фамилия, имя, отчество</li>
                                        <li>электронный адрес</li>
                                        <li>номера телефонов</li>
                                    </ul>
                                </td>
                                <td>Федеральный закон «Об информации, информационных технологиях и о защите информации» от 27.07.2006 N 149-ФЗ</td>
                                <td>Сбор, запись, систематизация, накопление, хранение, уничтожение и обезличивание персональных данных</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="policy-section contact-section">
                <h2>Контактная информация</h2>
                <p>По всем вопросам, касающимся обработки персональных данных, вы можете обратиться к Оператору:</p>
                <div class="contact-info">
                    <p><strong>Электронная почта:</strong> <a href="mailto:info@dosatron.online">info@dosatron.online</a></p>
                    <p><strong>Веб-сайт:</strong> <a href="https://dosatron.online/">https://dosatron.online/</a></p>
                    <p><strong>Политика в сети Интернет:</strong> <a href="https://dosatron.online/privacy">https://dosatron.online/privacy</a></p>
                </div>
                <p class="update-info"><em>Политика действует бессрочно до замены ее новой версией. Актуальная версия всегда доступна по указанной ссылке.</em></p>
            </section>

        </div>
    </div>
</section>

<?php include "includes/footer.php"; ?>
</body>
</html>