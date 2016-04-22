package ru.prokatvros.veloprokat.model.db;

import com.activeandroid.Model;
import com.activeandroid.annotation.Column;
import com.activeandroid.annotation.Table;
import com.activeandroid.query.Select;
import com.google.gson.annotations.Expose;


@Table(name = "PlanExchange")
public class PlanExchange extends Model {

    public static final int OPERATION_CREATE = 0;
    public static final int OPERATION_UPDATE = 1;
    public static final int OPERATION_DELETE = 2;

    @Expose
    @Column( name = "Date" )
    public long Date;

    @Expose
    @Column( name = "TableName" )
    public String TableName;

    @Expose
    @Column( name = "TypeChange" )
    public int TypeChange;

    @Expose
    @Column( name = "onId" )
    public long OnId;

    public static int getCount(){
        return new Select()
                .from(PlanExchange.class)
                .execute().size();
    }

}

