$ErrorActionPreference = 'Stop'

$catalog = [xml] (Invoke-WebRequest -Uri 'https://petshophanoi.com/sitemap_products_1.xml' -UseBasicParsing -TimeoutSec 30).Content
$namespace = New-Object System.Xml.XmlNamespaceManager($catalog.NameTable)
$namespace.AddNamespace('sm', 'http://www.sitemaps.org/schemas/sitemap/0.9')
$namespace.AddNamespace('im', 'http://www.google.com/schemas/sitemap-image/1.1')

$catalogImages = @{}
foreach ($node in $catalog.SelectNodes('//sm:url', $namespace)) {
    $pageNode = $node.SelectSingleNode('sm:loc', $namespace)
    $imageNode = $node.SelectSingleNode('im:image/im:loc', $namespace)
    if ($pageNode -and $imageNode) {
        $catalogImages[$pageNode.InnerText.TrimEnd('/')] = $imageNode.InnerText
    }
}

$sources = @(
    @{ Slug = 'bong-tap-gam-gan-day-danh-cho-cho' },
    @{ Slug = 'do-choi-cho-cho-day-thung-that-2-dau' },
    @{ Slug = 'do-choi-dia-bay-nhua-do-huan-luyen-thu-cung' },
    @{ Slug = 'bong-chut-chit-hinh-xuong' },
    @{ Slug = 'can-cau-thep' },
    @{ Url = 'https://www.petmate.com/cdn/shop/files/32495_MC_Wacky_Wool_Balls_2pk_FRONT.jpg?v=1731623764' },
    @{ Slug = 'ban-cao-mong-cho-meo-hinh-gon-song-tang-kem-catmint-cutepets' },
    @{ Url = 'https://cdn.cat-on.com/media/e3/75/ef/1763543206/katzentunnel-fuer-katzen-wellpappe-le-tube-l-fsc-zertifiziert-weiss-unifarben-000x.jpg?ts=1763543206' },
    @{ Url = 'https://gotags.com/cdn/shop/files/GT_AT_Silicone_Slider_Active_SUP_1_1200x1200.jpg?v=1766095605' },
    @{ Slug = 'hop-day-dat-tu-dong-diil' },
    @{ Slug = 'dai-lung-k9-chuyen-dung-cho-cho-nghiep-vu-cutepets' },
    @{ Slug = 'day-dat-kem-vong-co' },
    @{ Slug = 'o-dem-long-tron-day-50cm-x-20cm' },
    @{ Slug = 'dem-cho-cho-meo-hoa-tiet-chu-nhat-o-nem-cho-thu-cung-cutepets' },
    @{ Url = 'https://cdn.thecornishsurfer.com/media/2024/08/24160335/The-Cornish-Surfer-Pet-Blanket.jpg' },
    @{ Slug = 'nha-dem-nem-nam-dem-ni-hinh-nha-mai-ngoi-ong-khoi-cho-thu-cung' },
    @{ Url = 'https://pawsbetter.com/cdn/shop/files/Ceramic-Dog-Slow-Feeder-Bowl-01.webp?v=1741762360' },
    @{ Url = 'https://watadog.com/cdn/shop/files/Watadog_-_Bebedero_Portatil_Perros_Blanco.webp?v=1762340655' },
    @{ Slug = 'bat-doi-go-chong-gu-30x15x7-5cm' },
    @{ Slug = 'may-loc-nuoc-ong-vang-2l' },
    @{ Slug = 'luoc-chai-long-rung-htbrush' },
    @{ Slug = 'kim-cat-mong-cho-cho-meo' },
    @{ Slug = 'gang-tay-tam-vai' },
    @{ Slug = 'khan-tam-sieu-tham-clean-cham' },
    @{ Slug = 'ao-luoi-ba-lo-in-hinh-cute' },
    @{ Slug = 'ao-mua-cho-cho-meo-4-7kg' },
    @{ Url = 'https://radiantdogs.com/cdn/shop/products/dog-bandana-white-761952.jpg?v=1707270395' },
    @{ Slug = 'ao-len-gau' },
    @{ Slug = 'tui-van-chuyen-cho-cho-meo' },
    @{ Slug = 'tham-lot-o-to-xe-hoi-1-lop-chong-tham-nuoc-chong-tray-xuoc' },
    @{ Url = 'https://cocopuplondon.com/cdn/shop/products/6_c0f3f622-aff8-4a2c-aa55-0e084079bcd3.png?v=1660035773' },
    @{ Url = 'https://www.wagtrendz.com/cdn/shop/products/dog-water-bottles-918321.jpg?v=1752896024' }
)

$target = Join-Path $PSScriptRoot '..\public\media\products'
$headers = @{ 'User-Agent' = 'Mozilla/5.0' }

for ($index = 0; $index -lt $sources.Count; $index++) {
    $number = $index + 1
    $source = $sources[$index]
    $url = $source.Url
    if (-not $url) {
        $page = "https://petshophanoi.com/$($source.Slug)"
        $url = $catalogImages[$page]
    }
    if (-not $url) { throw "Không tìm thấy ảnh cho PAW-$('{0:D4}' -f $number)" }

    $extension = [IO.Path]::GetExtension(([uri] $url).AbsolutePath).TrimStart('.').ToLowerInvariant()
    if ($extension -eq 'jpeg') { $extension = 'jpg' }
    if ($extension -notin @('jpg', 'png', 'webp', 'gif')) { $extension = 'jpg' }
    $destination = Join-Path $target ('paw-{0:D4}.{1}' -f $number, $extension)

    Invoke-WebRequest -Uri $url -OutFile $destination -UseBasicParsing -TimeoutSec 30 -Headers $headers
    if ((Get-Item $destination).Length -lt 5000) { throw "Ảnh PAW-$('{0:D4}' -f $number) không hợp lệ" }
    Write-Output ('PAW-{0:D4} {1} {2}' -f $number, $extension, (Get-Item $destination).Length)
}
