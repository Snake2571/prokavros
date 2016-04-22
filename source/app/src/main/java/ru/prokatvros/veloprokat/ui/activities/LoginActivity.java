package ru.prokatvros.veloprokat.ui.activities;

import android.content.Intent;
import android.os.Bundle;
import android.support.v7.widget.Toolbar;
import android.util.Log;
import android.widget.Toast;


import com.activeandroid.query.Delete;
import com.activeandroid.query.Select;
import com.android.volley.NoConnectionError;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.Volley;
import com.google.gson.Gson;
import com.google.gson.GsonBuilder;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.List;

import ru.prokatvros.veloprokat.BikerentalApplication;
import ru.prokatvros.veloprokat.ConstantsBikeRentalApp;
import ru.prokatvros.veloprokat.R;
import ru.prokatvros.veloprokat.model.db.Admin;
import ru.prokatvros.veloprokat.model.db.Client;
import ru.prokatvros.veloprokat.model.db.PlanExchange;
import ru.prokatvros.veloprokat.model.requests.AuthorizationRequest;
import ru.prokatvros.veloprokat.model.requests.ClientRequest;
import ru.prokatvros.veloprokat.model.requests.LoadAllDataRequest;
import ru.prokatvros.veloprokat.model.requests.PostResponseListener;
import ru.prokatvros.veloprokat.ui.fragments.LoginFragment;
import ru.prokatvros.veloprokat.ui.fragments.SelectPointFragment;
import ru.prokatvros.veloprokat.utils.DataParser;


public class LoginActivity extends  BaseActivity implements LoginFragment.OnLoginListener {

    static final String TAG = "Auth";
    boolean canLoadDUMP;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_base);

        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        getSupportActionBar().hide();

        replaceFragment(new LoginFragment(), false);
    }

    @Override
    protected void onResume() {
        super.onResume();;
    }

    private void sendRegistrationIdToBackend(final String login, final String password) {

        AuthorizationRequest request = AuthorizationRequest.requestLogin(login, password,  new PostResponseListener() {
            @Override
            public void onResponse(String response) {
                Gson gson = new GsonBuilder().excludeFieldsWithoutExposeAnnotation().create();
                Log.d(TAG, "Response: " + response);
                if (response.contains("Invalid login or password")){
                    Toast.makeText(getBaseContext(), getString(R.string.invalid_login_or_password),Toast.LENGTH_LONG).show();
                    getProgressDialog().cancel();
                    return;
                }
                Admin admin = gson.fromJson(response, Admin.class);
                loadDB(admin);
            }

            @Override
            public void onErrorResponse(VolleyError error) {
                getProgressDialog().hide();
                if (error instanceof NoConnectionError){
                    Toast.makeText(LoginActivity.this, getString(R.string.server_connection_error), Toast.LENGTH_LONG).show();
                    List<Admin> adminList = new Select().from(Admin.class).where("login = ?", login).where("pass = ?", password).execute();
                    if (adminList.size() > 0){
                        BikerentalApplication.getInstance().setAdmin(adminList.get(0));
                        replaceFragment(new SelectPointFragment(), false);
                    }
                    else
                    {
                        Toast.makeText(getBaseContext(), getString(R.string.invalid_login_or_password),Toast.LENGTH_LONG).show();
                        return;
                    }
                }
                else{
                    Toast.makeText(LoginActivity.this, "Ошибка: "+error.toString(), Toast.LENGTH_LONG).show();
                }
                error.printStackTrace();
            }
        });

        getProgressDialog().setMessage(getString(R.string.authorisation));
        getProgressDialog().show();

        Volley.newRequestQueue(this).add(request);
    }

    protected void loadDB(final Admin admin) {
        getProgressDialog().setMessage(getString(R.string.update_data));

        LoadAllDataRequest requestCollectData = LoadAllDataRequest.requestCollectData(new Response.Listener<String>() {
            @Override
            public void onResponse(String response) {
                try {
                    JSONObject jsonData = new JSONObject(response);

                    if ( jsonData.getInt("error_encode") == 0 ) {
                        String url = ConstantsBikeRentalApp.URL_SERVER + jsonData.getJSONObject("data").getString("url");

                        if (!canLoadDUMP) {
                            Toast.makeText( LoginActivity.this , getString(R.string.can_not_load_dump_from_server), Toast.LENGTH_LONG ).show();
                        }
                        loadDumpDB(url, admin);
                    }
                    else{
                        getProgressDialog().cancel();
                        Toast.makeText(getBaseContext(), getString(R.string.refresh_base_server_side_error),Toast.LENGTH_LONG).show();
                        return;
                    }
                } catch (JSONException ex) {
                    ex.printStackTrace();
                    getProgressDialog().hide();
                }
            }
        }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {
                error.printStackTrace();
                getProgressDialog().hide();
            }
        });
        Volley.newRequestQueue(this).add(requestCollectData);
    }

    protected void loadDumpDB(String url, final Admin admin) {
        getProgressDialog().setMessage(getString(R.string.load_data));

        BikerentalApplication.getInstance().getDataParser().loadDumpDB(url, new DataParser.OnLoadDBListener() {
            @Override
            public void onLoad() {}

            @Override
            public void onFinish() {
                admin.save();
                BikerentalApplication.getInstance().setAdmin(admin);
                replaceFragment(new SelectPointFragment(), false);
                getProgressDialog().hide();
            }

            @Override
            public void onError() {
                Toast.makeText(getBaseContext(), getString(R.string.loadDumpDB_error),Toast.LENGTH_LONG).show();
                admin.save();
                BikerentalApplication.getInstance().setAdmin(admin);
                replaceFragment(new SelectPointFragment(), false);
                getProgressDialog().hide();
            }

        });

    }

    @Override
    protected int getContainer() {
        return R.id.container;
    }

    protected boolean SendNotSandableData(){
        List<PlanExchange> listPlanExchange;
        boolean prAllOk = true;
        if (PlanExchange.getCount() > 0){
            listPlanExchange = new Select().from(PlanExchange.class).execute();
            for (PlanExchange plan : listPlanExchange){
                switch (plan.TableName){
                    case "Client":
                        SendClient(plan);
                        break;
                }
            }
            if (PlanExchange.getCount() > 0) prAllOk = !prAllOk;
        }
        return prAllOk;
    }

    private void SendClient(PlanExchange plan) {
        final Client client = Client.load(Client.class,plan.OnId);
        ClientRequest clientRequest;
        switch (plan.TypeChange){
            case PlanExchange.OPERATION_CREATE:
                                                clientRequest = ClientRequest.requestPostClient(client, new PostResponseListener() {
                                                    @Override
                                                    public void onResponse(String response) {
                                                        Log.d(TAG, "Post response: " + response);
                                                        try {
                                                            JSONObject jsonResponse = new JSONObject(response);
                                                            int error_code = jsonResponse.getInt("error_code");

                                                            if (error_code != ClientRequest.CODE_NOT_ERROR_POST_CLIENT) {
                                                                Log.d(TAG, "ERROR: error_code = " + error_code);
                                                            }

                                                            new Delete().from(PlanExchange.class).where("onId = " + client.serverId).execute();

                                                        } catch (JSONException ex) {
                                                            Log.d(TAG, "ERROR: " + ex);
                                                            ex.printStackTrace();
                                                        }
                                                    }
                                                    @Override
                                                    public void onErrorResponse(VolleyError error) {
                                                        error.printStackTrace();
                                                        Log.d(TAG, "ERROR:" + error);

                                                    }
                                                });
                                                Volley.newRequestQueue(this).add(clientRequest);
                                                break;
            case PlanExchange.OPERATION_UPDATE:
                                                clientRequest = ClientRequest.requestPutClient(client, new PostResponseListener() {
                                                    @Override
                                                    public void onResponse(String response) {
                                                        Log.d(TAG, "Put response: " + response);
                                                        try {
                                                            JSONObject jsonResponse = new JSONObject(response);
                                                            int error_code = jsonResponse.getInt("error_code");
                                                            if (error_code != ClientRequest.CODE_NOT_ERROR_POST_CLIENT) {
                                                                Log.d(TAG, "ERROR: error_code = " + error_code);
                                                            }
                                                            new Delete().from(PlanExchange.class).where("onId = "+client.getId()).execute();
                                                        } catch (JSONException ex) {
                                                            ex.printStackTrace();
                                                            return;
                                                        }
                                                    }
                                                    @Override
                                                    public void onErrorResponse(VolleyError error) {
                                                        error.printStackTrace();
                                                        Log.d(TAG, "Put error: ");
                                                    }
                                                });
                                                Volley.newRequestQueue(this).add(clientRequest);
                                                break;
        }
    }

    @Override
    public void onLogin(String login, String password) {
        //// TODO: 20.04.2016 Добавлено для отладки. копирует существующую базу на карту памяти
        MainActivity.exportDatabse(ConstantsBikeRentalApp.DB_NAME);
        canLoadDUMP = SendNotSandableData();
        sendRegistrationIdToBackend(login, password);
    }
}