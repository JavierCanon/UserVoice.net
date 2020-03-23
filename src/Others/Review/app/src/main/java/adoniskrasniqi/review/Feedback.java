package adoniskrasniqi.review;

/**
 * Created by Adonis Krasniqi on 20-11-2017.
 * e-Mail: adoniskras97@gmail.com
 */

public class Feedback {
    private int shop_id;
    private int grade;
    private String comment;

    public Feedback(int shop_id, int grade, String comment) {
        this.shop_id = shop_id;
        this.grade = grade;
        this.comment = comment;
    }
    public String toString() {
        return shop_id + "," + grade + "," + comment;
    }
}
