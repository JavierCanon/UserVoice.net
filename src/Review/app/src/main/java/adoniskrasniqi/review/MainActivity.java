package adoniskrasniqi.review;

/**
 * Created by Adonis Krasniqi on 03-11-2017.
 * e-Mail: adoniskras97@gmail.com
 */

import android.graphics.drawable.Drawable;
import android.os.Bundle;
import android.os.Handler;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.View;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.EditText;
import android.widget.FrameLayout;
import android.widget.ImageButton;

import com.android.volley.DefaultRetryPolicy;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import java.util.HashMap;
import java.util.Map;
import java.util.Timer;
import java.util.TimerTask;


public class MainActivity extends AppCompatActivity {

    final private String url = "https://github.com/";
    final private String authKey = "d7b3438d97f335e612a566a731eea5acb8fe83c8";
    final private String token = "0fdfceef022a595193f44acca7a99e97e61ffc27";

    public boolean voted = false;
    Drawable defaultBackgorund;
    private ImageButton b1;
    private ImageButton b2;
    private ImageButton b3;
    private ImageButton b4;
    private ImageButton b5;
    private EditText comment_box;
    private Button sendButton;
    private Button retry;
    private boolean hideFrame = true;
    /*
    * Feedback
    * */
    final private int shop = 1;
    private int grade;
    private String comment;
    private Feedback[] feedbacks = new Feedback[10];
    private int position = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        //Set app as fullscreen
        getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN, WindowManager.LayoutParams.FLAG_FULLSCREEN);
        //Prevent screen from turning off
        getWindow().addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON);

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        //Initialize assets
        initialize();
        //Set background of clicked Star button, and change [grade] value
        set_rateButton_click();

        // Call onSubmit() method when Send button is clicked
        sendButton.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v) {
                onSubmit();
            }
        });
        // Call retryFeedback() method when Retry button is clicked
        retry.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v) {
                retryFeedback();
            }
        });

    }


    /*
    *   Initialize assets
    * */
    private void initialize() {
        b1 = (ImageButton) findViewById(R.id.b1);
        b2 = (ImageButton) findViewById(R.id.b2);
        b3 = (ImageButton) findViewById(R.id.b3);
        b4 = (ImageButton) findViewById(R.id.b4);
        b5 = (ImageButton) findViewById(R.id.b5);
        comment_box = (EditText) findViewById(R.id.comment_box);
        sendButton = (Button) findViewById(R.id.sendButton);
        retry = (Button) findViewById(R.id.retry);
        defaultBackgorund = getResources().getDrawable(R.drawable.rpl);
    }


    /*
    *   This method is used to change background of clicked Star button
    *   And to change [grade] based on clicked star
    * */
    private void set_rateButton_click(){
        b1.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v) {   grade = 1;  if (!voted) {   displayFrame(1);  voted = true;  }
            }
        });

        b2.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v) {   grade = 2;  if (!voted) {   displayFrame(2);  voted = true;  }
            }
        });

        b3.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v) {   grade = 3;  if (!voted) {   displayFrame(3);  voted = true;  }
            }
        });

        b4.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v) {   grade = 4;  if (!voted) {   displayFrame(4);  voted = true;  }
            }
        });

        b5.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v) {   grade = 5;  if (!voted) {   displayFrame(5);  voted = true;  }
            }
        });
    }


    /*
    *  This method is called to return app to default values
    **/
    private void retryFeedback() {
        comment_box.setText("");
        comment_box.setVisibility(View.GONE);

        retry.setVisibility(View.GONE);
        sendButton.setVisibility(View.GONE);

        b1.setVisibility(View.VISIBLE);
        b2.setVisibility(View.VISIBLE);
        b3.setVisibility(View.VISIBLE);
        b4.setVisibility(View.VISIBLE);
        b5.setVisibility(View.VISIBLE);

        b1.setBackground(defaultBackgorund);
        b2.setBackground(defaultBackgorund);
        b3.setBackground(defaultBackgorund);
        b4.setBackground(defaultBackgorund);
        b5.setBackground(defaultBackgorund);

        voted = false;
    }


    /*
    *   This method is called when user clicks Send button
    *   It creates a Volley POST_request, with a timeout of 5 seconds
    *
    *   If request is successful, clear the array where Feedback's are stored
    *   If request is not successful, Log the Error.
    *               Feedback is stored in Feedback's array so it is not deleted when request is unsuccessful
    *               And the next time users click Send button, the previous unsuccessful Feedback
    *                   will be in the Feedback's array and will be sent to server
    *
    *
    *
    *   Parameters are sent as String
    *   authKey is sent to validate request
    *
    *   [hideFrame] is used to display "Thank you message" for longer period of time
    *       if request is not completed in less than 6 seconds,
    *       [hideFrame] extends "Thank you message" time to 12 seconds before going invisible
    *
    * */
    private void send_data() {
        hideFrame = false;
        RequestQueue queue = Volley.newRequestQueue(this);
        StringRequest postRequest = new StringRequest(Request.Method.POST, url,
                new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        clearFeedbacks();
                        hideFrame = true;
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        Log.e("Error.Response", error.toString());
                        hideFrame = true;
                    }
                }
        ) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();

                //  Add to [params] each item stored in Feedback's array
                for (int i = 0; i < position; i++)
                    params.put("feedback[" + i + "]", feedbacks[i].toString());

                params.put("auth_key", authKey);
                params.put("token", token);
                return params;
            }
        };
        postRequest.setRetryPolicy(new DefaultRetryPolicy(
                11000, // Timeout
                0,  // Number of retries = 0, if number of retries>0 in slow connection uploads same data twice.
                DefaultRetryPolicy.DEFAULT_BACKOFF_MULT));
        queue.add(postRequest);
    }


    /*
    *  This method is called when users click Send button
    *  Store comment box value to String [comment]
    *
    *  Add this Feedback to Feedback's array
    *  Call method 'send_data()' to initialize a Volley request and send data to server
    *
    *  Call method 'retryFeedback()' to return app to default values
    *
    *  Call method 'display_thankYou()' to display message
    * */
    private void onSubmit() {
        comment = comment_box.getText().toString();
        add_to_feedback(shop, grade, comment);
        send_data();
        retryFeedback();
        display_thankYou();
    }

    /*
    *   Call this method to clear Feedback's array
    * */
    private void clearFeedbacks() {
        position = 0;
        feedbacks = new Feedback[10];
    }

    /*
    *   Call this method to add a Feedback to Feedback's array
    *   If feedback is full, extend it
    *
    *   Raise [position]
    * */
    private void add_to_feedback(int shop, int grade, String comment) {
        if (position == feedbacks.length)
            extendFeedbacks();
        feedbacks[position] = new Feedback(shop, grade, comment);
        position++;
    }

    /*
    *   Call this method to extend Feedback's array length
    *
    *   Create a temporary array with length [position+10]
    *   Fill temporary array with Feedback's array values
    *
    *   Replace Feedback's array with temporary array;
    * */
    private void extendFeedbacks() {
        Feedback[] f = new Feedback[position + 10];
        System.arraycopy(feedbacks, 0, f, 0, position);
        feedbacks = null;
        feedbacks = f;
    }

    /*
    *   When user votes we call this method
    *   it displays the "Thank You message" ,hidden frame ,
     *   for 6-12 Seconds, then it's visibility is gone
    * */
    private void display_thankYou() {
        FrameLayout framelayout = (FrameLayout) findViewById(R.id.framelayout);
        framelayout.setVisibility(View.VISIBLE);

        final Handler handler = new Handler();
        Timer t = new Timer();
        t.schedule(new TimerTask() {
            public void run() {
                handler.post(new Runnable() {
                    public void run() {
                        if(hideFrame) {
                            FrameLayout framelayout = (FrameLayout) findViewById(R.id.framelayout);
                            framelayout.setVisibility(View.INVISIBLE);
                        }
                        else
                            display_thankYou();
                    }
                });
            }
        }, 6000);
    }

    /*
    *   Fill clicked Star button background and buttons on it's left
    *   Also display Comment box, Send button and Retry button
    * */
    private void displayFrame(int i) {
        if (voted)
            return;

        comment_box.setVisibility(View.VISIBLE);
        sendButton.setVisibility(View.VISIBLE);
        retry.setVisibility(View.VISIBLE);

        if (i == 1) {
            b1.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
        } else if (i == 2) {
            b1.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
            b2.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
        } else if (i == 3) {
            b1.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
            b2.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
            b3.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
        } else if (i == 4) {
            b1.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
            b2.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
            b3.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
            b4.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
        } else if (i == 5) {
            b1.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
            b2.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
            b3.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
            b4.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
            b5.setBackgroundColor(ContextCompat.getColor(this, R.color.imageButtonColor));
        }
    }
}











