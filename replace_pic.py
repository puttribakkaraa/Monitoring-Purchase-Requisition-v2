import os

path = r"d:\PT MTM\Monitoring Purchase Requisition\app\Http\Controllers\DashboardController.php"
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

# Replace all occurrences of self::PIC_MAP
content = content.replace("self::PIC_MAP", "self::getPicMap()")

old_def = """    const getPicMap() = [
        'DAA' => 'Bapak Imam',
        'DAG' => 'Bapak Imam',
        'DAB' => 'Ibu Ani',
        'DAE' => 'Ibu Ani',
        'DAC' => 'Ibu Yani',
        'DAD' => 'Ibu Yani',
        'DAF' => 'Ibu Yani',
        'DAH' => 'FINSA',
    ];"""

original_def = """    const PIC_MAP = [
        'DAA' => 'Bapak Imam',
        'DAG' => 'Bapak Imam',
        'DAB' => 'Ibu Ani',
        'DAE' => 'Ibu Ani',
        'DAC' => 'Ibu Yani',
        'DAD' => 'Ibu Yani',
        'DAF' => 'Ibu Yani',
        'DAH' => 'FINSA',
    ];"""

new_def = """    private static $picMapCache = null;

    public static function getPicMap()
    {
        if (self::$picMapCache === null) {
            self::$picMapCache = \App\Models\Department::pluck('pic_name', 'code')->toArray();
        }
        return self::$picMapCache;
    }

    public function updateDepartmentPic(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'dept_code' => 'required',
            'pic_name' => 'required|max:255'
        ]);

        \App\Models\Department::updateOrCreate(
            ['code' => $request->dept_code],
            ['pic_name' => $request->pic_name]
        );

        self::$picMapCache = null;

        return response()->json([
            'success' => true,
            'message' => 'PIC berhasil diupdate',
            'pic_name' => $request->pic_name
        ]);
    }"""

if original_def in content:
    content = content.replace(original_def, new_def)
elif old_def in content:
    content = content.replace(old_def, new_def)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)
print("Replaced successfully")
