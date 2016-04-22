package ru.prokatvros.veloprokat.utils;

import ru.prokatvros.veloprokat.ConstantsBikeRentalApp;
import ru.prokatvros.veloprokat.utils.DataParser;
import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;

/**
 * Created by Александр on 18.04.2016.
 */
public class SQLiteDBWork {

    static DBHelper dbHelper;

    static Context context;

    public static void checkDataBase(Context context)
    {
        dbHelper = new DBHelper(context);
    }

    static class DBHelper extends SQLiteOpenHelper {
        public DBHelper(Context context){
            super(context, ConstantsBikeRentalApp.DB_NAME_OLD, null, 1);
        }

        @Override
        public void onCreate(SQLiteDatabase db) {

        }

        @Override
        public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {

        }
    }

}
