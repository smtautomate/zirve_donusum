namespace e_İrsaliyeDemo_v1._0._0
{
    partial class MethodParameters
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.dpBeginDate = new System.Windows.Forms.DateTimePicker();
            this.dpEndDate = new System.Windows.Forms.DateTimePicker();
            this.Label1 = new System.Windows.Forms.Label();
            this.label2 = new System.Windows.Forms.Label();
            this.chkIsNew = new System.Windows.Forms.CheckBox();
            this.SuspendLayout();
            // 
            // dpBeginDate
            // 
            this.dpBeginDate.CustomFormat = "dd.MM.yyyy HH:mm";
            this.dpBeginDate.Enabled = false;
            this.dpBeginDate.Format = System.Windows.Forms.DateTimePickerFormat.Custom;
            this.dpBeginDate.Location = new System.Drawing.Point(160, 87);
            this.dpBeginDate.Margin = new System.Windows.Forms.Padding(4);
            this.dpBeginDate.Name = "dpBeginDate";
            this.dpBeginDate.Size = new System.Drawing.Size(235, 22);
            this.dpBeginDate.TabIndex = 15;
            this.dpBeginDate.Value = new System.DateTime(2017, 9, 18, 0, 0, 0, 0);
            // 
            // dpEndDate
            // 
            this.dpEndDate.CustomFormat = "dd.MM.yyyy HH:mm";
            this.dpEndDate.Enabled = false;
            this.dpEndDate.Format = System.Windows.Forms.DateTimePickerFormat.Custom;
            this.dpEndDate.Location = new System.Drawing.Point(160, 117);
            this.dpEndDate.Margin = new System.Windows.Forms.Padding(4);
            this.dpEndDate.Name = "dpEndDate";
            this.dpEndDate.Size = new System.Drawing.Size(235, 22);
            this.dpEndDate.TabIndex = 15;
            this.dpEndDate.Value = new System.DateTime(2017, 9, 18, 0, 0, 0, 0);
            // 
            // Label1
            // 
            this.Label1.AutoSize = true;
            this.Label1.Location = new System.Drawing.Point(48, 87);
            this.Label1.Name = "Label1";
            this.Label1.Size = new System.Drawing.Size(105, 17);
            this.Label1.TabIndex = 16;
            this.Label1.Text = "BaşlangıçTarihi";
            // 
            // label2
            // 
            this.label2.AutoSize = true;
            this.label2.Location = new System.Drawing.Point(48, 117);
            this.label2.Name = "label2";
            this.label2.Size = new System.Drawing.Size(70, 17);
            this.label2.TabIndex = 16;
            this.label2.Text = "BitişTarihi";
            // 
            // chkIsNew
            // 
            this.chkIsNew.AutoSize = true;
            this.chkIsNew.Location = new System.Drawing.Point(160, 155);
            this.chkIsNew.Name = "chkIsNew";
            this.chkIsNew.Size = new System.Drawing.Size(119, 21);
            this.chkIsNew.TabIndex = 17;
            this.chkIsNew.Text = "Yeni Faturalar";
            this.chkIsNew.TextAlign = System.Drawing.ContentAlignment.TopCenter;
            this.chkIsNew.UseVisualStyleBackColor = true;
            // 
            // MethodParameters
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(8F, 16F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(460, 588);
            this.Controls.Add(this.chkIsNew);
            this.Controls.Add(this.label2);
            this.Controls.Add(this.Label1);
            this.Controls.Add(this.dpEndDate);
            this.Controls.Add(this.dpBeginDate);
            this.Name = "MethodParameters";
            this.Text = "MethodParameters";
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.DateTimePicker dpBeginDate;
        private System.Windows.Forms.DateTimePicker dpEndDate;
        private System.Windows.Forms.Label Label1;
        private System.Windows.Forms.Label label2;
        private System.Windows.Forms.CheckBox chkIsNew;
    }
}