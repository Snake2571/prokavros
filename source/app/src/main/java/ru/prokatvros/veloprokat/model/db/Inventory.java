package ru.prokatvros.veloprokat.model.db;

import android.content.Context;
import android.os.Parcel;
import android.os.Parcelable;

import com.activeandroid.ActiveAndroid;
import com.activeandroid.Model;
import com.activeandroid.annotation.Column;
import com.activeandroid.annotation.Table;
import com.activeandroid.query.Select;
import com.google.gson.GsonBuilder;
import com.google.gson.annotations.Expose;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import ru.prokatvros.veloprokat.R;

@Table(name = "Inventory")
public class Inventory extends Model implements Parcelable {

    public static String TAG = "INVENTORY";

    public static final Long MAIN_GROUP = 1l;
    public static final Long ADDITIONAL_GROUP = 2l;

    public static final int RENTED_STATE = 1;
    public static final int REFIT_STATE = 2;
    public static final int MISSING_STATE = 3;
    public static final int FREE_STATE = 4;

    @Expose
    @Column(name = "ServerId", unique = true, onUniqueConflict = Column.ConflictAction.REPLACE)
    public String serverId;

    @Expose
    @Column(name = "Model")
    public String model;

    @Expose
    @Column( name = "Number" )
    public String number;

    @Expose
    @Column( name = "NumberFrame" )
    public String numberFrame;

    @Expose
    @Column( name = "State" )
    public int state = FREE_STATE;

    @Expose
    @Column(name = "CountRent")
    private int count_rent = 0;

    /*@Expose
    @Column(name = "Cost")
    public int summ = -1; */

    @Expose
    @Column(name = "Tarif", onDelete = Column.ForeignKeyAction.CASCADE)
    public Tarif tarif;

    @Expose
    @Column(name = "Avatar")
    public String avatar;

    @Expose
    @Column(name = "Point" /*, onDelete = Column.ForeignKeyAction.CASCADE*/ )
    private Point points;

    @Expose
    @Column(name = "IdGroup")
    private Long idGroup;

    @Expose
    @Column(name = "IdParent")
    public Long idParent;

    public boolean hasAvatar() {
        return avatar != null;
    }


    public int getCountRent() {
        return count_rent;
    }

    public void setCountRent(int countRent) {
        this.count_rent = countRent;
    }

    public String toJsonString() {
        return new GsonBuilder().excludeFieldsWithoutExposeAnnotation().create().toJson(this);
    }


    public void saveCrud() {

        Point points;
        if ( this.points != null && this.points.address != null ) {

            points = Point.getByAddress(this.points.address);
            if ( points == null )
                this.points.save();
            else
                this.points = points;
        }
    }

    public static List<Inventory> getAll() {

        return new Select()
                .from(Inventory.class)
                .orderBy("IdParent asc")
                .execute();
    }

    public static List<Inventory> getRoot() {
        return new Select()
                .from(Inventory.class)
                .where("IdParent = ?", 0)
                .execute();
    }

    public static Inventory getByNumber(String number) {
        return new Select()
                .from(Inventory.class)
                .where("Number = ?", number)
                .executeSingle();
    }

    public static List<Inventory> getByName(String model) {
        return new Select()
                .from(Inventory.class)
                .where("Model = ?", model)
                .execute();
    }

    public static List<Inventory> getBySubName(String subName) {
        return new Select()
                .from(Inventory.class)
                .where("Model LIKE ?", "%" + subName + "%")
                .execute();
    }

    public static List<Inventory> getBySubNumber(String subNumber) {
        return new Select()
                .from(Inventory.class)
                .where("Number LIKE ?", "%" + subNumber + "%")
                .execute();
    }

    public Long fullSave() {
        if (this.tarif != null )
            this.tarif.save();
        return  this.save();
    }

    public static void parse(List<Inventory> list) {

        ActiveAndroid.beginTransaction();
        try {
            for (Inventory inventory : list) {
                inventory.fullSave();
            }
            ActiveAndroid.setTransactionSuccessful();
        }
        finally {
            ActiveAndroid.endTransaction();
        }
    }


    private static List<Inventory> removeRootItem(List<Inventory> allInventory) {

        List<Inventory> filteredList = new ArrayList<>();
        for (Inventory inventory : allInventory) {
            if (inventory.idParent != 0)
                filteredList.add(inventory);
        }

        return filteredList;
    }

    public static List<Inventory> getBySubNumber( String subNumber, Long idGroup ) {
        List<Inventory> allInventory = new Select()
                .from(Inventory.class)
                .where("Number LIKE ?", "%" + subNumber + "%")
                .where("IdGroup = ?", String.valueOf(idGroup))
                .execute();

        return removeRootItem(allInventory);
    }

    public static List<Inventory> getByState(int state) {
        List<Inventory> allInventory = new Select()
                .from(Inventory.class)
                .where("State = ?", state)
                .execute();

        return removeRootItem(allInventory);
    }

    public static List<Inventory> getFreeBySubNumber(String subNumber, Long idGroup) {
        /*List<Inventory> inventoryList = getBySubNumber(subNumber, idGroup);

        List<Rent> rentList = Rent.getAllByCompleted(false);

        for (Rent rent : rentList) {
            if (inventoryList != null)
                if (inventoryList.remove(rent.inventory)) {
                    Log.d(TAG, "WAS REMOVE !!!!!");
                } else {
                    Log.d(TAG, "not remove");
                }
        }

        return inventoryList; */


        List<Inventory> allInventory = new Select()
                .from(Inventory.class)
                .where("State = ?", FREE_STATE)
                .where("Number LIKE ?", "%" + subNumber + "%")
                .where("IdGroup = ?", String.valueOf(idGroup))
                .execute();

        return removeRootItem(allInventory);

    }


    public static List<Inventory> getByPoint( Point point, boolean isKeepRoot ) {

        List<Inventory> byPoint = new Select()
                .from(Inventory.class)
                .orderBy("IdParent asc")
                .where( "Point = ?", point.getId() )
                .execute();

        return isKeepRoot ? byPoint : removeRootItem(byPoint);
    }

    /*public static List<Inventory> getByGroup(Long idGroup) {
        return new Select()
                .from( Inventory.class )
                .where( "IdGroup = ?", idGroup )
                .execute();
    } */

    private static Map<String, Integer> keyStateMap;

    private static void initKeyMap(Context context) {
        keyStateMap = new HashMap<>();
        keyStateMap.put(context.getString(R.string.item_rented), Inventory.RENTED_STATE);
        keyStateMap.put(context.getString(R.string.item_missing), Inventory.MISSING_STATE);
        keyStateMap.put(context.getString(R.string.item_refit), Inventory.REFIT_STATE);
        keyStateMap.put(context.getString(R.string.item_free), Inventory.FREE_STATE);

    }

    public static String getStateStringFormat(Context context, int state) {
        if (keyStateMap == null)
            initKeyMap(context);

        for (Map.Entry<String, Integer> e : keyStateMap.entrySet())
            if ( state == e.getValue() )
                return e.getKey();

        throw new Error("Set unavialable object in map !!!");
    }

    public static final Parcelable.Creator<Inventory> CREATOR = new Parcelable.Creator<Inventory>() {

        public Inventory createFromParcel(Parcel in) {

            return Inventory.load(Inventory.class, in.readLong());
        }

        public Inventory[] newArray(int size) {
            return new Inventory[size];
        }
    };


    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeLong(getId());
    }

}
