<?php

namespace App\Http\Controllers\Admin\Site;

use App\Model\Site\Sitemap;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemaps = Sitemap::all();
        $logs = User::select(DB::raw('Count(id) as amount, DATE(created_at) as created_at'))
            ->where('status','imported')
            ->groupBy(DB::raw('DATE(created_at)'))
        ->get();
        return view('admin.Sitemap.index',compact('sitemaps','logs'));
    }

    public function create()
    {

    }

    public function make()
    {
        \request()->validate([
            'from'=>'required',
            'to'=>'required'
        ]);
        date_default_timezone_set('Asia/Tehran');

        $root_path = $_SERVER['DOCUMENT_ROOT'];
        $path =  "{$root_path}/sitemaps/";
        $name = "sitemap" . Carbon::now()->format('Y_m_d_is') . ".xml";
        $file = $path . $name;
        $file_handle = fopen($file, 'w+')

        or die("خطا: سطح دسترسی برای ویرایش فایل در سرور تنظیم نیست!");

        $empty = "";

        $string_data = $empty;
        fwrite($file_handle, $string_data);

        $start = "<?xml version='1.0' encoding='UTF-8'?>
<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        $string_data = $start;
        fwrite($file_handle, $string_data);

//        $home = "<url><loc>https://sbm24.com</loc></url>\n\n";
//        $string_data = $home;
//        fwrite($file_handle, $string_data);


        $users = User::where('approve', 1)->where('doctor_status', 'active')
            ->where('status',\request()->input('type'))
            ->whereDate('created_at','>=',\request()->input('from'))
            ->whereDate('created_at','<=',\request()->input('to'))
            ->get();
        if (!$users->isEmpty()) {
            foreach ($users as $user) {
                $username = $user->username;
                $date = Carbon::instance($user->created_at)->format('Y-m-d');
                $lastmod = \request()->input('lastmod') ? \request()->input('lastmod') : $date;
                $changefreq = \request()->input('changefreq') ? \request()->input('changefreq') : "weekly";

                $url = "<url>
<loc>https://sbm24.com/$username</loc>
<lastmod>$lastmod</lastmod>
 <changefreq>$changefreq</changefreq>
 </url>\n";

                $string_data = $url;
                fwrite($file_handle, $string_data);
            }

            $end = "</urlset>";
            $string_data = $end;
            fwrite($file_handle, $string_data);
            fclose($file_handle);
            Sitemap::create([
                'from_' => \request()->input('from'),
                'to_' => \request()->input('to'),
                'amount' => $users->count(),
                'path' => asset('sitemaps') . '/' . $name,
            ]);
            return redirect()->back()->with(['success' => 'تعداد ' . $users->count() . ' آدرس با موفقیت ساخته شد']);
        }
        return redirect()->back()->with(['success' => 'تعداد ' . $users->count() . ' هیچ کابری در این بازه زمانی وجود ندارد']);
    }

    public function download($id)
    {
        $path = Sitemap::find($id)->path;
        //if (file_exists($path))
            return response()->download($path);
        //return redirect()->back()->with(['error'=>'فایل یافت نشد']);
    }

    public function reindex()
    {
        $xmlString=file_get_contents(\request()->file('file'));
         $xmlObject = simplexml_load_string($xmlString);
        $json = json_encode($xmlObject);
        $items = json_decode($json,TRUE);
        $items=$items['url'];
        $ids=[];
        foreach ($items as $item) {
            $item['name']=str_replace('https://sbm24.com/','',$item['loc']);
            $item['name']=str_replace('دکتر-','',$item['name']);
            $item['name2']= $item['name'];
            $item['name']=str_replace('-',' ',$item['name']);
            $item['name2']=str_replace('-','%',$item['name2']);

            $user = User::
            where(function ($query) use ($item){
                $query->orWhere('fullname','LIKE','%'.$item['name2'].'%')
                    ->orWhere('fullname','LIKE','%'.$item['name'].'%')
                    ->orWhere('username','LIKE',
                        str_replace('https://sbm24.com/','',$item['loc']));
            })
                ->where('approve',1)
                ->whereDate('updated_at','>=','2020-10-04')
                ->orderBy('created_at','DESC')
                ->first();

            if (!$user)
                dd($item,'No User');
            if ($user->username !== str_replace('https://sbm24.com/','',$item['loc']))
            {
                if ($user->approve==1) {
                    $user->username = str_replace('https://sbm24.com/', '', $item['loc']);
                    $user->status = 'imported';
                    $user->save();
                    array_push($ids, $user->mobile);
                }
            }
        }
        dd($ids);
    }
}
